<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinancialFile extends Model
{
    use SoftDeletes;

    /**
     * Valid entity types for polymorphic relationship
     */
    public const VALID_ENTITY_TYPES = [
        \App\Models\FinancialRevenue::class,
        \App\Models\FinancialExpense::class,
    ];

    protected $fillable = [
        'organization_id',
        'user_id',
        'file_name',
        'file_path',
        'file_url',
        'file_type',
        'mime_type',
        'file_size',
        'entity_type',
        'entity_id',
        'an',
        'luna',
        'tip',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'an' => 'integer',
        'luna' => 'integer',
    ];

    protected $appends = ['icon'];

    protected static function booted()
    {
        // Auto-fill organization_id and user_id
        static::creating(function ($file) {
            // Validate entity_type if provided
            if ($file->entity_type && !in_array($file->entity_type, self::VALID_ENTITY_TYPES)) {
                throw new \InvalidArgumentException(
                    "Invalid entity_type. Must be one of: " . implode(', ', self::VALID_ENTITY_TYPES)
                );
            }

            if (Auth::check()) {
                $file->organization_id = $file->organization_id ?? Auth::user()->organization_id;
                $file->user_id = $file->user_id ?? Auth::id();
            }

            // Auto-fill year, month, and type from linked entity
            if ($file->entity_type && $file->entity_id) {
                $entity = $file->entity;

                if ($entity) {
                    // Get year and month from the entity's occurred_at date
                    if (isset($entity->occurred_at)) {
                        $date = $entity->occurred_at;
                        $file->an = $file->an ?? $date->year;
                        $file->luna = $file->luna ?? $date->month;
                    }

                    // Auto-determine file type based on entity
                    if (!$file->tip) {
                        if ($entity instanceof \App\Models\FinancialRevenue) {
                            $file->tip = 'incasare';
                        } elseif ($entity instanceof \App\Models\FinancialExpense) {
                            $file->tip = 'plata';
                        }
                    }
                }
            }

            // If no entity linked, mark as general
            if (!$file->tip) {
                $file->tip = 'general';
            }

            // Default year/month to current if not set
            if (!$file->an) {
                $file->an = now()->year;
            }
            if (!$file->luna) {
                $file->luna = now()->month;
            }
        });

        // Global scope for organization and user isolation
        static::addGlobalScope('user_scope', function (Builder $query) {
            if (Auth::check()) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->where('user_id', Auth::id());
            }
        });

        // Delete physical file when model is deleted
        static::deleted(function ($file) {
            // Delete the physical file from storage using the 'financial' disk
            if ($file->file_path && Storage::disk('financial')->exists($file->file_path)) {
                Storage::disk('financial')->delete($file->file_path);

                // Auto-delete empty parent folders
                $disk = Storage::disk('financial');
                $directory = dirname($file->file_path);

                // Recursively check and delete empty folders
                // Stop at root or when encountering a non-empty folder
                while ($directory && $directory !== '.' && $directory !== '/' && $directory !== '') {
                    $files = $disk->files($directory);
                    $subdirs = $disk->directories($directory);

                    // If folder is completely empty (no files and no subdirectories), delete it
                    if (empty($files) && empty($subdirs)) {
                        $disk->deleteDirectory($directory);
                        $directory = dirname($directory);
                    } else {
                        // Folder is not empty, stop cleanup
                        break;
                    }
                }
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Expenses that were imported from this bank statement file
     */
    public function importedExpenses()
    {
        return $this->hasMany(FinancialExpense::class, 'source_file_id');
    }

    // Helper methods
    public function getFormattedSizeAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute()
    {
        return route('financial.files.download', $this->id);
    }

    public function getIconAttribute()
    {
        // Try to get extension from file_name first
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

        // If no extension in file_name, try file_path
        if (empty($extension) && $this->file_path) {
            $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        }

        // If still no extension, try to determine from mime_type
        if (empty($extension) && $this->mime_type) {
            $mimeToExt = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/zip' => 'zip',
                'application/x-rar-compressed' => 'rar',
            ];
            $extension = $mimeToExt[$this->mime_type] ?? '';
        }

        $icons = [
            'pdf' => '📄',
            'doc' => '📝',
            'docx' => '📝',
            'xls' => '📊',
            'xlsx' => '📊',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'zip' => '🗜️',
            'rar' => '🗜️',
        ];

        return $icons[$extension] ?? '📎';
    }
}
