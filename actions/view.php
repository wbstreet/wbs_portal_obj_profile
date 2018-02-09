<?php


include(__DIR__.'/../lib.class.portal_obj_profile.php');
$clsModPortalObjProfile = new ModPortalObjProfile($page_id, $section_id);

if ($admin->is_authenticated()) {$is_auth = true;}
else { $is_auth = false; }

if ($modPortalArgs['obj_id'] === null) { // здесь у нас не profile_id, а user_id.
    $modPortalArgs['obj_id'] = $admin->get_user_id();
}

?>

<?php

if ($modPortalArgs['obj_id'] === 'list') {

    $common_opts = [
    ];
    
    $opts = array_merge($common_opts, [
        'find_str'=>$modPortalArgs['s'],
        'limit_count'=>$modPortalArgs['obj_per_page'],
        'limit_offset'=>$modPortalArgs['obj_per_page'] * ($modPortalArgs['page_num']-1),
        'order_by'=>[$clsModPortalObjBlog->tbl_blog.'.`obj_id`'],
        'order_dir'=>'DESC',
    ]);
    /*$publications = $clsModPortalObjBlog->get_publication($opts);
    if (gettype($publications) == 'string') $clsModPortalObjBlog->print_error($publications);
    
    
    $objs = [];
    $page_link = page_link($wb->link);
    while (gettype($publications) !== 'string' && $publications !== null && $publication = $publications->fetchRow(MYSQLI_ASSOC)) {
        $publication['orig_image'] = ''; $publication['preview_image'] ='';
        if ($publication['image_storage_id'] !== null) {
            $publication['orig_image'] = $clsStorageImg->get($publication['image_storage_id'], 'origin');
            $publication['preview_image'] = $clsStorageImg->get($publication['image_storage_id'], '350x250');
        }
    
        $publication['publication_url'] = $page_link.'?obj_id='.$publication['obj_id'];
        $publication['publication_from_url'] = $page_link.'?obj_owner='.$publication['user_owner_id'];
        $publication['show_panel_edit'] = $is_auth && $publication['user_owner_id'] === $admin->get_user_id() ? true : false;
        $publication['user'] = $admin->get_user_details($publication['user_owner_id']);
        $objs[] = $publication;
    }

    $loader = new Twig_Loader_Array(array(
        'view' => file_get_contents(__DIR__.'/view.html'),
    ));
    $twig = new Twig_Environment($loader);
        
    echo $twig->render('view', [
        'is_auth'=>$is_auth,
        'objs'=>$objs,
    ]);*/

} else {
    
    $profile_id = $clsModPortalObjProfile->create_profile($section_id, $page_id, $modPortalArgs['obj_id']);
    $opts = [
        'obj_id'=>$profile_id,
    ];

    $profiles = $clsModPortalObjProfile->get_profile($opts);
    if (gettype($profiles) == 'string') echo $profiles;
    else if ($profiles->numRows() === 0) echo "Пользователь не найден";
    else {
        $profile = $profiles->fetchRow();
        
        if ($profile['is_active'] == '0' && $profile ['user_owner_id'] !== $admin->get_user_id()) {
            echo "Пользователь отключил свой аккаунт.";
        } else {
            
            $skills = [];
            $r = $clsModPortalObjProfile->get_skills(['user_id'=>$admin->get_user_id()]);
            if (gettype($r) === 'string') {$clsModPortalObjProfile->print_error($r); $r = null; }
            while($r !== null && $row = $r->fetchRow()) $skills[] = $row;

            $login_when = DateTime::createFromFormat('U', $profile['login_when']);
            $profile['login_when'] = $login_when->format('r');

            // отображаем

            $clsModPortalObjProfile->render('view.html', [
               'profile'=>$profile,
               'skills'=>$skills,
            ]);
            
        }
        
    }
    
}

?>