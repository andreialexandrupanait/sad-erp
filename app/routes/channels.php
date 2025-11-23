<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Organization-wide channel - users can listen to their organization's events
Broadcast::channel('organization.{organizationId}', function ($user, $organizationId) {
    return (int) $user->organization_id === (int) $organizationId;
});

// Task list channel - users in the organization can listen to list events
Broadcast::channel('list.{listId}', function ($user, $listId) {
    $list = \App\Models\TaskList::find($listId);
    return $list && (int) $user->organization_id === (int) $list->organization_id;
});

// User-specific channel for personal notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
