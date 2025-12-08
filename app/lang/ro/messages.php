<?php

/**
 * Traduceri mesaje - mesaje flash, notificări, confirmări
 */

return [
    // Mesaje de succes
    'saved' => 'Salvat.',
    'created_success' => 'Creat cu succes!',
    'updated_success' => 'Actualizat cu succes!',
    'deleted_success' => 'Șters cu succes!',
    'imported_success' => 'Importat cu succes!',
    'exported_success' => 'Exportat cu succes!',
    'settings_updated' => 'Setări actualizate cu succes!',
    'copied' => 'Copiat!',
    'link_copied' => 'Link copiat în clipboard!',

    // Mesaje de eroare
    'error_occurred' => 'A apărut o eroare. Vă rugăm încercați din nou.',
    'could_not_copy' => 'Nu s-a putut copia link-ul',
    'status_update_error' => 'Eroare la actualizarea statusului. Încercați din nou.',

    // Prompturi de confirmare
    'are_you_sure' => 'Sigur doriți?',
    'confirm_action' => 'Confirmă acțiunea',
    'confirm_delete' => 'Sigur doriți să ștergeți?',
    'action_cannot_undone' => 'Această acțiune nu poate fi anulată.',
    'confirm_delete_file' => 'Sigur doriți să ștergeți acest fișier?',
    'select_at_least_one' => 'Vă rugăm selectați cel puțin un element',
    'perform_bulk_action' => 'Sigur doriți să efectuați această acțiune asupra a :count element(e)?',

    // Confirmări specifice
    'confirm_delete_client' => 'Sigur doriți să ștergeți acest client?',
    'confirm_delete_domain' => 'Sigur doriți să ștergeți acest domeniu?',
    'confirm_delete_subscription' => 'Sigur doriți să ștergeți acest abonament?',
    'confirm_delete_credential' => 'Sigur doriți să ștergeți acest acces?',
    'confirm_delete_internal_account' => 'Sigur doriți să ștergeți acest cont intern?',
    'confirm_delete_revenue' => 'Sigur doriți să ștergeți acest venit?',
    'confirm_delete_expense' => 'Sigur doriți să ștergeți această cheltuială?',

    // Confirmări acțiuni în masă
    'confirm_delete_clients' => 'Sigur doriți să ștergeți clienții selectați? Această acțiune nu poate fi anulată.',
    'confirm_delete_domains' => 'Sigur doriți să ștergeți domeniile selectate? Această acțiune nu poate fi anulată.',
    'confirm_delete_subscriptions' => 'Sigur doriți să ștergeți abonamentele selectate? Această acțiune nu poate fi anulată.',
    'confirm_delete_expenses' => 'Sigur doriți să ștergeți cheltuielile selectate? Această acțiune nu poate fi anulată.',
    'confirm_delete_revenues' => 'Sigur doriți să ștergeți veniturile selectate? Această acțiune nu poate fi anulată.',
    'confirm_export_clients' => 'Exportați clienții selectați în CSV?',
    'confirm_export_domains' => 'Exportați domeniile selectate în CSV?',
    'confirm_export_subscriptions' => 'Exportați abonamentele selectate în CSV?',
    'confirm_export_expenses' => 'Exportați cheltuielile selectate în CSV?',
    'confirm_toggle_autorenew' => 'Sigur doriți să comutați reînnoirea automată pentru domeniile selectate?',
    'confirm_renew_subscriptions' => 'Reînnoiți abonamentele selectate? Datele următoarelor reînnoiri vor fi avansate conform ciclurilor de facturare.',
    'confirm_update_status_to' => 'Actualizați statusul clienților selectați la :status?',

    // Succes acțiuni în masă
    'clients_updated' => 'Statusurile clienților au fost actualizate cu succes!',
    'clients_exported' => 'Clienții au fost exportați cu succes!',
    'clients_deleted' => 'Clienții au fost șterși cu succes!',
    'domains_exported' => 'Domeniile au fost exportate cu succes!',
    'domains_deleted' => 'Domeniile au fost șterse cu succes!',
    'autorenew_toggled' => 'Reînnoirea automată a fost comutată cu succes!',
    'subscriptions_renewed' => 'Abonamentele au fost reînnoite cu succes!',
    'subscriptions_exported' => 'Abonamentele au fost exportate cu succes!',
    'subscriptions_deleted' => 'Abonamentele au fost șterse cu succes!',
    'expenses_exported' => 'Cheltuielile au fost exportate cu succes!',
    'expenses_deleted' => 'Cheltuielile au fost șterse cu succes!',

    // Reînnoire abonamente
    'confirm_subscription_renewal' => 'Confirmă reînnoirea abonamentului',
    'renewal_date_advanced' => 'Data următoarei reînnoiri va fi avansată conform ciclului de facturare.',
    'error_renewing' => 'Eroare la reînnoirea abonamentului.',
    'error_renewing_with_msg' => 'Eroare la reînnoirea abonamentului: :message',

    // Mesaje fișiere
    'file_too_large' => ':name este prea mare. Dimensiunea maximă este 10MB.',
    'unsupported_file_type' => ':name are un tip de fișier nesuportat.',
    'file_duplicate_saved_as' => 'Un fișier cu acest nume există deja. Salvat ca: :name',
    'file_deleted' => 'Fișier șters cu succes',
    'files_deleted' => ':count fișiere șterse cu succes',
    'no_files_selected' => 'Niciun fișier selectat',
    'file_renamed' => 'Fișier redenumit cu succes',

    // Schimbare status
    'click_to_change_status' => 'Click pentru schimbare status',
    'click_to_set_status' => 'Click pentru setare status',
    'select_status' => 'Selectați status...',
    'change_status' => 'Schimbă Status',
    'clear_status' => 'Șterge Status',

    // Stări goale
    'no_clients' => 'Niciun client',
    'no_domains' => 'Niciun domeniu',
    'no_subscriptions' => 'Niciun abonament',
    'no_credentials' => 'Nicio parolă',
    'no_internal_accounts' => 'Niciun cont intern',
    'no_revenues' => 'Niciun venit',
    'no_expenses' => 'Nicio cheltuială',
    'get_started_client' => 'Începe prin a crea primul client',
    'get_started_domain' => 'Începe prin a crea primul domeniu',
    'get_started_subscription' => 'Începe prin a crea primul abonament',
    'get_started_credential' => 'Începe prin a crea primul acces',
    'get_started_internal_account' => 'Începe prin a crea primul cont intern',
    'get_started_revenue' => 'Începe prin a crea primul venit',
    'get_started_expense' => 'Începe prin a crea prima cheltuială',

    // În curând
    'coming_soon' => 'În curând',
    'coming_soon_desc' => 'Această pagină de setări este în curs de dezvoltare. Va fi disponibilă într-o actualizare viitoare.',

    // Acțiuni în masă
    'items_not_found_or_access' => 'Unele elemente nu au fost găsite sau nu aveți acces la ele.',
    'no_permission_update_all' => 'Nu aveți permisiunea de a actualiza toate elementele selectate.',
    'items_updated_count' => ':count elemente actualizate cu succes.',
    'no_permission_export_all' => 'Nu aveți permisiunea de a exporta toate elementele selectate.',
    'subscriptions_renewed_count' => ':count abonamente reînnoite',
    'subscriptions_updated_count' => ':count abonamente actualizate',

    // Cheltuieli/Venituri
    'expense_created' => 'Cheltuială creată cu succes!',
    'expense_added' => 'Cheltuială adăugată cu succes.',
    'expense_updated' => 'Cheltuială actualizată cu succes!',
    'expense_deleted' => 'Cheltuială ștearsă cu succes.',
    'revenue_created' => 'Venit creat cu succes!',
    'revenue_added' => 'Venit adăugat cu succes.',
    'revenue_updated' => 'Venit actualizat cu succes!',
    'revenue_deleted' => 'Venit șters cu succes.',

    // Import
    'import_started' => 'Import început! Se procesează :count rânduri în fundal. Puteți urmări progresul mai jos.',
    'import_completed' => 'Import finalizat: :imported cheltuieli importate, :skipped ignorate',
    'import_failed' => 'Importul a eșuat. Verificați formatul fișierului.',
    'import_not_found' => 'Import negăsit',
    'unauthorized' => 'Neautorizat',
    'import_cancel_running_only' => 'Doar importurile în rulare sau în așteptare pot fi anulate',
    'import_cancelled' => 'Import anulat cu succes',
    'import_delete_running' => 'Nu se poate șterge un import în rulare. Anulați-l mai întâi.',
    'import_deleted' => 'Import șters cu succes',
    'invalid_setting_category' => 'Categorie de setări invalidă',

    // Fișiere
    'files_uploaded_single' => ':count fișier încărcat cu succes',
    'files_uploaded_plural' => ':count fișiere încărcate cu succes',
    'no_files_for_month' => 'Nu există fișiere pentru luna selectată.',
    'no_files_for_year' => 'Nu există fișiere pentru anul selectat.',
    'zip_create_error' => 'Nu s-a putut crea arhiva ZIP.',
    'no_permission_view_password' => 'Nu aveți permisiunea de a vizualiza această parolă.',

    // Acces module
    'no_module_access' => 'Nu aveți permisiunea de a :action modulul :module.',
];
