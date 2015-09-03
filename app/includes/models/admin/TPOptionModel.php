<?php
/**
 * Created by PhpStorm.
 * User: freeman
 * Date: 20.08.15
 * Time: 15:17
 */
namespace app\includes\models\admin;
abstract class TPOptionModel extends \core\models\TPOOptionModel{
    private static $instance;
    private static $result;
    public function save_option($input)
    {
        // TODO: Implement save_option() method.
        if (!isset(self::$instance)) {
            self::$instance = true;
            if(isset($input['wizard'])){
                \app\includes\TPPlugin::$options['account']['marker'] = $input['account']['marker'];
                \app\includes\TPPlugin::$options['account']['token'] = $input['account']['token'];
                \app\includes\TPPlugin::$options['local']['localization'] = $input['local']['localization'];
                \app\includes\TPPlugin::$options['local']['currency'] = $input['local']['currency'];
                self::$result = \app\includes\TPPlugin::$options;
            }else{
                self::$result = array_merge(\app\includes\TPPlugin::$options, $input);
            }
            \app\includes\TPPlugin::deleteCacheAll();

        }
        return self::$result;
    }
}