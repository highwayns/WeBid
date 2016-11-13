<?php
/***************************************************************************
 *   copyright				: (C) 2008 - 2016 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

define('InAdmin', 1);
$current_page = 'contents';
include '../common.php';
include INCLUDE_PATH . 'functions_admin.php';
include 'loggedin.inc.php';

// Insert new message
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    if (empty($_POST['question'][$system->SETTINGS['defaultlanguage']]) || empty($_POST['answer'][$system->SETTINGS['defaultlanguage']])) {
        $template->assign_block_vars('alerts', array('TYPE' => 'error', 'MESSAGE' => $ERR_067));
    } else {
        $query = "INSERT INTO " . $DBPrefix . "faqs values (NULL, :question, :answer, :category)";
        $params = array();
        $params[] = array(':question', $system->cleanvars($_POST['question'][$system->SETTINGS['defaultlanguage']]), 'str');
        $params[] = array(':answer', $system->cleanvars($_POST['answer'][$system->SETTINGS['defaultlanguage']], true), 'str');
        $params[] = array(':category', $_POST['category'], 'int');
        $db->query($query, $params);
        $id = $db->lastInsertId();
        // Insert into translation table
        foreach ($LANGUAGES as $lang_code) {
            $query = "INSERT INTO ".$DBPrefix."faqs_translated VALUES (:id, :lang, :question, :answer)";
            $params = array();
            $params[] = array(':id', $id, 'int');
            $params[] = array(':lang', $lang_code, 'str');
            $params[] = array(':question', $system->cleanvars($_POST['question'][$lang_code]), 'str');
            $params[] = array(':answer', $system->cleanvars($_POST['answer'][$lang_code], true), 'str');
            $db->query($query, $params);
        }
        header('location: faqs.php');
        exit;
    }
}

// Get data from the database
$query = "SELECT * FROM " . $DBPrefix . "faqscategories";
$db->direct_query($query);

while ($row = $db->fetch()) {
    $template->assign_block_vars('cats', array(
            'ID' => $row['id'],
            'CATEGORY' => $row['category']
            ));
}

foreach ($LANGUAGES as $k => $language) {
    $template->assign_block_vars('lang', array(
            'LANG' => $language,
            'TITLE' => (isset($_POST['title'][$k])) ? $_POST['title'][$k] : '',
            'CONTENT' => (isset($_POST['content'][$k])) ? $_POST['content'][$k] : ''
            ));
}

include 'header.php';
$template->set_filenames(array(
        'body' => 'newfaq.tpl'
        ));
$template->display('body');
include 'footer.php';
