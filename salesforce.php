<?php
/*** get webinar csv ***/
$csv = array_map('str_getcsv', file('/home/rell/Downloads/webinar-jan2.csv'));
$leadCsv = array_map('str_getcsv', file('/home/rell/Downloads/allleads-1.csv'));
$contactCsv = array_map('str_getcsv', file('/home/rell/Downloads/allcontacts-1.csv'));

$campaign = '7010h000001IT6fAAG';
$campaignStatus = 'Responded';
$eventName = '1-5 Webinar';

foreach ($leadCsv as $k => $v) {
    if ($k !== 0) {
        $leads[$v[0]] = $v[1];
    }
}

foreach ($contactCsv as $k => $v) {
    if ($k !== 0) {
        $contacts[$v[1]] = $v[2];
    }
}

$columns = [
    'first' => 9,
    'last' => 10,
    'org' => 11,
    'job' => 12,
    'email' => 14,
    'phone' => 13,
    'demo' => 24
];

$headRows = [
    'First Name',
    'Last Name',
    'Company',
    'Title',
    'Email',
    'Phone',
    'Admin Notes',
    'Lead Source',
];

$campaignRows = [
    'CampaignId',
    'CampaignStatus'
];

$insertLeadRows = array_merge($headRows,['Follow Up', 'Owner', 'CampaignId', 'CampaignStatus']);
$updateLeadRows = array_merge($headRows, ['Response', 'Lead Id', 'Owner', 'CampaignId', 'CampaignStatus']);
$updateContactRows = array_merge($headRows, ['Response', 'Lead', 'Contact ID', 'Owner', 'CampaignId', 'CampaignStatus']);

$insertLeads[] = $insertLeadRows;
$updateLeads[] = $updateLeadRows;
$updateContacts[] = $updateContactRows;
$yes = 0;

foreach($csv as $k => $v) {
    /*** Format Name ***/
    if (!in_array($k, [0,1])) {
        $record = [];
        /* First Name */
        $record[0] = ucwords($v[$columns['first']]);
        /* Last Name */
        $record[1] = ucwords($v[$columns['last']]);
        /* Organization */
        $record[2] = ucwords($v[$columns['org']]);
        /* Job Title */
        $record[3] = ucwords($v[$columns['job']]);
        /* Email */
        $record[4] = $v[$columns['email']];
        /* Phone */
        $record[5] = preg_replace('/[^0-9.]+/', '', $v[$columns['phone']]);
        /* Admin Notes */
        $record[6] = $eventName;
        /* Lead Source */
        $record[7] = 'Webinar';
        /* Demo Response */
        $record[8] = $v[$columns['demo']];
        if (strtolower($v[$columns['demo']]) == 'yes') {
            $yes++;
            $demoYes = true;
        } else {
            $demoYes = false;
        }

        $email = $v[$columns['email']];
        $leadId = (array_key_exists($email, $leads)) ? $leads[$email] : '';
        $contactId = (array_key_exists($email, $contacts)) ? $contacts[$email] : '';

        if (!empty($contactId)) {
            /* 9 - leadId, 10 - ContactID, 11 - Owner */
            $record[9] = '#N/A';
            $record[10] = $contactId;
            if ($demoYes == true && ($yes % 3 == 0)) {
                /* Every third yes goes to charles F00580000001foaX */
                $record[11] = '2F00580000001foaX';
            } else {
                /* Else contact goes to shawn 2F00580000001w9qG */
                $record[11] = '2F00580000001w9qG';
            }
            $record[12] = $campaign;
            $record[13] = $campaignStatus;
            $updateContacts[] = $record;
         } else {
             if (!empty($leadId)) {
                 /* 9 - leadId, 10 - Owner */
                 $record[9] = $leadId;
                 if ($demoYes == true && ($yes % 3 == 0)) {
                     /* Every third yes goes to charles F00580000001foaX */
                     $record[10] = '00580000001foaX';
                 } else {
                     /* Else contact goes to shawn 2F00580000001w9qG */
                     $record[10] = '00580000001w9qG';
                 }
                 $record[11] = $campaign;
                 $record[12] = $campaignStatus;
                 $updateLeads[] = $record;
             } else {
                 /* 9 Owner */
                 if ($demoYes == true && ($yes % 3 == 0)) {
                     /* Every third yes goes to charles F00580000001foaX */
                     $record[9] = '00580000001foaX';
                 } else {
                     /* Else contact goes to shawn 2F00580000001w9qG */
                     $record[9] = '00580000001w9qG';
                 }
                 $record[10] = $campaign;
                 $record[11] = $campaignStatus;
                 $insertLeads[] = $record;
             }
         }
    }
}

$fp = fopen('/home/rell/Downloads/leadsToInsert.csv', 'w');

foreach ($insertLeads as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

$fp = fopen('/home/rell/Downloads/leadsToUpdate.csv', 'w');

foreach ($updateLeads as $k => $row) {
    fputcsv($fp, $row);
}

fclose($fp);

$fp = fopen('/home/rell/Downloads/contactsToUpdate.csv', 'w');

foreach ($updateContacts as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);




