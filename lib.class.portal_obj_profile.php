<?php

$path_core = __DIR__.'/../wbs_portal/lib.class.portal.php';
if (file_exists($path_core )) include($path_core );
else echo "<script>console.log('Модуль wbs_portal_obj_profile требует модуль wbs_portal')</script>";

// используется только в данном файле. Пока неизвестно, включать её в sql_tools.php или нет.
if (!function_exists('guess_operator')) {
function guess_operator($value, $inverse=false) {
        if ($value === 'NULL') {
                if ($inverse) return ' is not ';
                else {return ' is ';}
        } else {
                if ($inverse) return '!=';
                else {return '=';}
        }
}
}

if (!class_exists('ModPortalObjProfile')) { 
class ModPortalObjProfile extends ModPortalObj {

    function __construct($page_id, $section_id) {
        parent::__construct('profile', 'Личная страница', $page_id, $section_id);
        $this->tbl_profile = "`".TABLE_PREFIX."mod_{$this->prefix}profile`";
        $this->tbl_profile_skill = "`".TABLE_PREFIX."mod_{$this->prefix}profile_skill`";
        $this->tbl_profile_skill_user = "`".TABLE_PREFIX."mod_{$this->prefix}profile_skill_user`";
        $this->tbl_wb_users = "`".TABLE_PREFIX."users`";
        $this->clsStorageImg = new WbsStorageImg();
    }

    function uninstall() {
        global $database;
        
        // проверяем наличие объектов

        /*$r = select_row($this->tbl_apartment, 'COUNT(`obj_id`) as ocount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['ocount'] > 0) return "Существуют объекты!";*/
        
        // проверяем, наличие партнёров

        /*$r = select_row($this->tbl_partner, 'COUNT(`partner_id`) as pcount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['pcount'] > 0) return "Существуют партнёры!";*/

        // проверяем, наличие категорий

        /*$r = select_row($this->tbl_category, 'COUNT(`category_id`) as ccount');
        if ($r === false) return "Неизвестная ошибка!";
        if ($r->fetchRow()['ccount'] > 0) return "Существуют категории!";*/

        // удаляем модуль

        $arr = [/*"DROP TABLE ".$this->tbl_apartment,
                "DROP TABLE ".$this->tbl_category,
                "DROP TABLE ".$this->tbl_partner,*/
        ];

        $r = parent::uninstall($arr);
        if ($r === false) return "Неизвестная ошибка!";
        if ($r !== true) return $r;
        
        return true;
        
    }
    
    function install() {
        return parent::install();
    }
    
    function create_profile($page_id, $section_id, $user_id) {
        global $database, $admin;

        $r = select_row($this->tbl_profile, '*', $this->tbl_profile.'.`user_id`='.process_value($user_id));
        if (gettype($r) === 'string') return $r;
        if ($r !== null) return (integer)($r->fetchRow()['obj_id']);

        $fields = [
            'section_id'=>$section_id,
            'page_id'=>$page_id,
            'obj_type_id'=>$this->obj_type_id,
            'user_id'=>$user_id,
            'user_owner_id' => $user_id,
        ];

        $_fields = $this->split_arrays($fields);

        $r = insert_row($this->tbl_obj_settings, $_fields);
        if ($r !== true) return "Неизвестная ошибка";

        $profile_id = $database->getLastInsertId();

        $fields['obj_id'] = $profile_id;
        $r = insert_row($this->tbl_profile, $fields);
        if ($r !== true) return "Неизвестная ошибка";

        return $profile_id;
    }

    /*function update_publication($publication_id, $fields) {
        global $database;

        $_fields = $this->split_arrays($fields);

        $r = $this->get_publication(['obj_id'=>$publication_id]);
        if (gettype($r) === 'string') return $r;
        if ($r === null) return 'Запись не найдена (id: '.$database->escapeString($publication_id).')';


        if ($_fields) {
            $r = update_row($this->tbl_obj_settings, $_fields, glue_fields(['obj_id'=>$publication_id], 'AND'));
            if ($r !== true) return $r;
        }

        if ($fields) {
            $r = update_row($this->tbl_blog, $fields, glue_fields(['obj_id'=>$publication_id], 'AND'));
            if ($r !== true) return 'Неизвестная ошибка';
        }
        
        return true;
    }*/

    function get_obj($sets=[], $only_count=false) {
        global $database;

        $where = [
            "{$this->tbl_profile}.`obj_id`={$this->tbl_obj_settings}.`obj_id`",
            "{$this->tbl_obj_settings}.`obj_type_id`=".process_value($this->obj_type_id),
            "{$this->tbl_profile}.`user_id`={$this->tbl_wb_users}.`user_id`"
        ];
        $this->_getobj_where($sets, $where);

        if (isset($sets['user_id']) && $sets['user_id'] !== null) $where[] = "{$this->tbl_profile}.`user_id`=".process_value($sets['user_id']);

        $where = implode(' AND ', $where);
        $select = $only_count ? "COUNT(obj_id) AS count" : "*";
        $order_limit = $this->_getobj_order_limit($sets);

        $sql = "SELECT $select FROM {$this->tbl_profile}, {$this->tbl_obj_settings}, {$this->tbl_wb_users} WHERE $where $order_limit";
        
        //return $sql;
        //echo "<script>console.log(`".htmlentities($sql)."`);</script>";

        return $this->_getobj_return($sql, $only_count);
    }
    
    function get_skills($sets=[]) {
        $where = ["{$this->tbl_profile_skill_user}.`skill_id`={$this->tbl_profile_skill}.`skill_id`"];
        
        if (isset($sets['user_id']) && $sets['user_id'] !== null) $where[] = "{$this->tbl_profile_skill_user}.`user_id`=".process_value($sets['user_id']);

        $tables = [$this->tbl_profile_skill_user, $this->tbl_profile_skill];

        return select_row($tables, '*', implode(' AND ', $where));
    }
    
}
}