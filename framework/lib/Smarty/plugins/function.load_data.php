<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {load_data} function plugin
 *
 * Type:     function<br>
 * Name:     eval<br>
 * Purpose:  evaluate a template variable as a template<br>
 * @link http://smarty.php.net/manual/en/language.function.eval.php {eval}
 * @author 小蔡 <cyy0523xc@gmail.com>
 * @param array
 * @param Smarty
 */

include 'cls_crud.php';


function smarty_function_load_data($params, &$smarty)
{
   // $class = (!isset($params['class']) || empty($params['class'])) ? 'cls_crud' : trim($params['class']);
    (!isset($params['table']) || empty($params['table'])) && exit('`table` is empty!');
    $db = new cls_crud(array('table' => $params['table']));
   // var_dump($params);
    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $db->get_block_list(array($params['where']), $params['limit']));
    }

}

/* vim: set expandtab: */

?>
