<?php


include(__DIR__.'/../lib.class.portal_obj_profile.php');
$clsModPortalObjProfile = new ModPortalObjProfile($page_id, $section_id);

if ($admin->is_authenticated()) {$is_auth = true;}
else { $is_auth = false; }

if ($modPortalArgs['obj_id'] === null) { // здесь у нас не profile_id, а user_id.
    $modPortalArgs['obj_id'] = $admin->get_user_id();
}



function get_info($clsModPortalObj, $method, $obj_id) {
    $objs = [];
    $obj_count = 0;
    $obj_exists = true;

    if ($clsModPortalObj->obj_type_is_active === '1') {

        // количество записей
    
        $obj_count = $clsModPortalObj->$method([
            'user_owner_id'=>$modPortalArgs['obj_id'],
            'is_active'=>1,
            'is_deleted'=>0,
            'is_created'=>'1',
            ], true);
        if (gettype($blog_count) === 'string') { $clsModPortalObjBlog->print_error($obj_count); $obj_exists = false;}
    
        // первые пять записей
        
        $_objs = $clsModPortalObj->$method([
            'user_owner_id'=>$modPortalArgs['obj_id'],
            'is_active'=>1,
            'is_deleted'=>0,
            'limit_count'=>5,
            'order_by'=>'date_created',
            'order_dir'=>'DESC',
            'is_created'=>'1',
            ]);
        if (gettype($_objs) === 'string') { $clsModPortalObj->print_error($_objs); $obj_exists = false;}
        while($_objs !== null && $obj = $_objs->fetchRow()) {
    
            // вынимакем страницу блога
    
            $r = select_row(TABLE_PREFIX.'pages', '`link`', '`page_id`='.process_value($obj['page_id']));
            if (gettype($r) === 'string') $clsModPortalObj->print_error($r);
            else if ($r == null) $clsModPortalObj->print_error('Страница не найдена!');
            else {
                $r = $r->fetchRow();
                $obj['obj_url'] = page_link($r['link'])."?obj_id=".$obj['obj_id'];
            }
    
            $objs[] = $obj;
        }

    }
    
    return [$objs, $obj_count, $obj_exists];

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
    if (gettype($profiles) === 'string') echo $profiles;
    else if ($profiles === null || $profiles->numRows() === 0) echo "Пользователь не найден";
    else {
        $profile = $profiles->fetchRow();
        
        if ($profile['is_active'] == '0' && $profile ['user_owner_id'] !== $admin->get_user_id()) {
            echo "Пользователь отключил свой аккаунт.";
        } else {
            
            // получаем специализацию
            
            $skills = [];
            $r = $clsModPortalObjProfile->get_skills(['user_id'=>$admin->get_user_id()]);
            if (gettype($r) === 'string') {$clsModPortalObjProfile->print_error($r); $r = null; }
            while($r !== null && $row = $r->fetchRow()) $skills[] = $row;

            // получаем блоги

            $path_blog = WB_PATH.'/modules/wbs_portal_obj_blog/lib.class.portal_obj_blog.php';
            if (file_exists($path_blog)) {
                include_once($path_blog);
                $clsModPortalObjBlog = new ModPortalObjBlog($section_id, $page_id);
                list($blogs, $blog_count, $blog_exists) = get_info($clsModPortalObjBlog, 'get_publication', $modPortalArgs['obj_id']);
            } else { list($blogs, $blog_count, $blog_exists) = [[], 0, false]; }
            
            // получаем проекты
            
            $path_project = WB_PATH.'/modules/wbs_portal_obj_project/lib.class.portal_obj_project.php';
            if (file_exists($path_project)) {
                include_once($path_project);
                $clsModPortalObjProject = new ModPortalObjProject($section_id, $page_id);            
                list($projects, $project_count, $project_exists) = get_info($clsModPortalObjProject, 'get_project', $modPortalArgs['obj_id']);
            } else { list($projects, $project_count, $project_exists) = [[], 0, false]; }

            // получаем недвижимости
            
            $path_apartment = WB_PATH.'/modules/wbs_portal_obj_estate/lib.class.portal_obj_estate.php';
            if (file_exists($path_apartment)) {
                include_once($path_apartment);
                $clsModPortalObjEstate = new ModPortalObjEstate($section_id, $page_id);            
                list($apartments, $apartment_count, $apartment_exists) = get_info($clsModPortalObjEstate, 'get_apartment', $modPortalArgs['obj_id']);
            } else { list($apartments, $apartment_count, $apartment_exists) = [[], 0, false]; }

            // конвертируем дату предыдущего входа

            $profile['login_when'] = DateTime::createFromFormat('U', $profile['login_when']);
            
            // отображаем

            $can_edit = $is_auth && $admin->get_user_id() === $profile['user_owner_id'] ? true : false;

            $clsModPortalObjProfile->render('view.html', [
               'profile'=>$profile,
               'skills'=>$skills,

               'blogs'=>$blogs,
               'blog_count'=>$blog_count,
               'blog_exists'=>$blog_exists,
               //'blog_max_count'=>$blog_max_count,

               'projects'=>$projects,
               'project_count'=>$project_count,
               'project_exists'=>$project_exists,
               //'project_max_count'=>$project_max_count,
    
               'apartments'=>$apartments,
               'apartment_count'=>$apartment_count,
               'apartment_exists'=>$apartment_exists,
               //'apartment_max_count'=>$apartment_max_count,  
               
               'can_edit'=>$can_edit,
            ]);
            
        }
        
    }
    
}

?>