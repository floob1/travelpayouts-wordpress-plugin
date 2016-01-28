<?php
/**
 * Created by PhpStorm.
 * User: freeman
 * Date: 28.01.16
 * Time: 10:40
 */

namespace app\includes\controllers\admin\menu;


class TPAutoReplacLinksController extends \core\controllers\TPOAdminMenuController
{
    public $model;
    public $modelOption;
    public $data;
    public function __construct()
    {
        parent::__construct();
        $this->model = new \app\includes\models\admin\menu\TPAutoReplacLinksModel();
        $this->modelOption = new \app\includes\models\admin\menu\TPAutoReplacLinksOptionModel();

    }
    public function action()
    {
        // TODO: Implement action() method.
        $plugin_page = add_submenu_page( TPOPlUGIN_TEXTDOMAIN,
            _x('Substitution links',  'add_menu_page page title', TPOPlUGIN_TEXTDOMAIN ),
            _x('Substitution links',  'add_menu_page page title', TPOPlUGIN_TEXTDOMAIN ),
            'manage_options',
            'tp_control_substitution_links',
            array(&$this, 'render'));
        add_action( 'admin_footer-'.$plugin_page, array(&$this, 'TPLinkHelp') );
    }

    public function render()
    {
        // TODO: Implement render() method.
        $action = isset($_GET['action']) ? $_GET['action'] : null ;
        $pathView = "";
        switch($action){
            case "add_link":
                $pathView = TPOPlUGIN_DIR."/app/includes/views/admin/menu/TPAutoReplacLinksAdd.view.php";
                break;
            default:
                $this->data = $this->model->get_data();
                $pathView = TPOPlUGIN_DIR."/app/includes/views/admin/menu/TPAutoReplacLinks.view.php";
                break;
        }
        parent::loadView($pathView);
    }
}