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
        add_action( 'save_post', array( &$this, 'autoReplacLinksSavePost'), 10, 3 );
        add_filter( 'wp_insert_post_data', array( &$this, 'autoReplacLinksInsertPost'), 10, 2 );
        add_action('add_meta_boxes', array( &$this, 'tp_add_custom_box'));
        //$this->model->getDataAutoReplacLinks();

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
            case "save_link":
                if(isset($_POST)){
                    $this->model->insert($_POST);
                }
                $this->redirect('admin.php?page=tp_control_substitution_links');
                break;
            case "edit_link":
                if(isset($_GET['id']) && !empty($_GET['id'])){
                    $this->data = $this->model->get_dataID((int)$_GET['id']);
                    $pathView = TPOPlUGIN_DIR."/app/includes/views/admin/menu/TPAutoReplacLinksEdit.view.php";
                }else{
                    $this->redirect('admin.php?page=tp_control_substitution_links');
                }
                break;
            case "update_link":
                if(isset($_POST)){
                    $this->model->update($_POST);
                }
                $this->redirect('admin.php?page=tp_control_substitution_links');
                break;
            default:
                $this->data = $this->model->get_data();
                $pathView = TPOPlUGIN_DIR."/app/includes/views/admin/menu/TPAutoReplacLinks.view.php";
                break;
        }
        parent::loadView($pathView);
    }


    /**
     *
     */
    public function tp_add_custom_box(){
        $screens = array( 'post', 'page' );
        foreach ( $screens as $screen ){
            add_meta_box(
                'tp_sectionid',
                _x('Substitution links',  'meta_box_post', TPOPlUGIN_TEXTDOMAIN ),
                array( &$this, 'tp_add_custom_box_callback'),
                $screen,
                'side',
                'high'
            );
        }

    }

    /**
     * @param $post
     */
    public function tp_add_custom_box_callback($post){
        //error_log(print_r($post, true));
        if(empty(get_post_meta( $post->ID, 'tp_auto_replac_link', true ))) {
            $tp_auto_replac_link = 0;
        }else{
            $tp_auto_replac_link = get_post_meta( $post->ID, 'tp_auto_replac_link', true );
        }
        // Используем nonce для верификации
        wp_nonce_field( TPOPlUGIN_NAME, 'tp_auto_replac_link_noncename' );
        ?>
        <fieldset>
            <legend class="screen-reader-text">
                <?php echo _x('Substitution links',  'meta_box_post', TPOPlUGIN_TEXTDOMAIN ); ?>
            </legend>
            <input type="radio" name="tp_auto_replac_link"
                   class="tp-auto-replac-link" id="tp-auto-replac-link-0" value="0"
                    <?php checked( $tp_auto_replac_link, 0 ); ?> >
            <label for="tp-auto-replac-link-0" class="tp-auto-replac-link-icon">
                <?php _e('Enable', TPOPlUGIN_TEXTDOMAIN ); ?>
            </label>
            <br><input type="radio" name="tp_auto_replac_link"
                       class="tp-auto-replac-link" id="tp-auto-replac-link-1" value="1"
                        <?php checked( $tp_auto_replac_link, 1 ); ?>>
            <label for="tp-auto-replac-link-1" class="tp-auto-replac-link-icon">
                <?php _e('Disable', TPOPlUGIN_TEXTDOMAIN ); ?>
            </label>
        </fieldset>
        <?php
    }

    /**
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function autoReplacLinksSavePost($post_id, $post, $update){
        //error_log(print_r($post_id, true));
        //error_log(print_r($post, true));
        //error_log(print_r($update, true));
        if ( ! isset( $_POST['tp_auto_replac_link_noncename'] ) )
            return $post_id;
        if ( ! wp_verify_nonce( $_POST['tp_auto_replac_link_noncename'], TPOPlUGIN_NAME ) )
            return $post_id;
        if ( $post->post_status == 'auto-draft' ||
            $post->post_status == 'draft' ||
            $post->post_status == 'trash' ){
            return $post_id;
        }
        $tp_auto_replac_link = sanitize_text_field( $_POST['tp_auto_replac_link'] );
        // Обновляем данные в базе данных.
        update_post_meta( $post_id, 'tp_auto_replac_link', $tp_auto_replac_link );


    }

    /**
     * Очищенные данные поста.
     * @param $data
     * Оригинальные данные поста переданные в $_POST
     * @param $postarr
     *
     * @return mixed
     * 'publish' - страница или запись опубликована.
     * 'pending' - пост ожидает утверждения администратора.
     * 'draft' - запись имеет статус черновика.
     * 'auto-draft' - новый пост, без контента.
     * 'future' - запись будет опубликована в будущем.
     * 'private' - личное, запись не буде показана неавторизованным посетителям.
     * 'inherit' - ревизия. См.get_children.
     * 'trash' - пост находится в Корзине. Добавлено в WordPress версии 2.9.
     *
     */
    public function autoReplacLinksInsertPost($data, $postarr){
        if ( $data['post_status'] == 'auto-draft' ||
            $data['post_status'] == 'draft' ||
            $data['post_status'] == 'trash' ){
            return $data;
        }
        if(empty($data['post_content'])) return $data;
        //error_log(print_r($data['post_content'], true));
        $dataAutoReplacLinks = array(
            'anchor' => array(
                'test',
                'test111'
            )
        );
        if(isset($postarr['tp_auto_replac_link']) && $postarr['tp_auto_replac_link'] == 0){
            $post_content = $data['post_content'];

            // Заменяемый текст
            $find = 'test111';
            $replace = 'test11122';

            // Сначала ищем теги <a>
            $tags = array();
            if (preg_match_all( // (?!.*?<\/[aA](\s*)? >.*?)
                "/<[aA](?:\s[^>]*)?>.*?<\/[aA](?:\s*)?>/",
                $post_content,$matches,PREG_OFFSET_CAPTURE//+PREG_SET_ORDER
            ))
            {
                foreach($matches[0] as $tagA)
                    $tags[] = array($tagA[1],$tagA[1]+strlen($tagA[0]));
            }

            if (preg_match_all("/$find/",
                $post_content,$matches,PREG_OFFSET_CAPTURE//+PREG_SET_ORDER
            ))
            {
                $len = strlen($find);
                // переворачиваем массив для замены с конца, чтобы сдвиг не мешал
                foreach(array_reverse($matches[0]) as $found) {
                    $pos = $found[1];
                    $inTag = false;
                    foreach($tags as $tagPos)
                        if ($tagPos[0]<$pos && $pos<$tagPos[1]) {
                            $inTag = true;
                            break;
                        }
                    if ( ! $inTag)
                        $post_content = substr_replace($post_content, $replace, $pos, $len);
                }
            }
            error_log($post_content);
            //$match = preg_match_all('/test111/',$data['post_content'], $search);
            /*$match = preg_replace(
                '/test111/',//[^>][^<] [^\<a.*?\>]test111[^\<\/a\>]
                '<a href="">test111</a> ',
                $data['post_content'],
                -1,
                $count);*/
            /*$data['post_content'] = preg_replace_callback(
                '/(^test111$)[^<a.*?>(test111)<\/a>]/m',//|(\b)test111(\b)(test111)|^test111|test111|test111$
                array( &$this, 'tp_preg_replace'),
                $data['post_content'],
                -1,
                $count
            );
            //error_log(print_r($match, true));
            error_log(print_r($count, true));
            /*$data['post_content'] = preg_replace(
                '|[^<a.*?>]test111[^<\/a>]|',//[^>][^<] [^\<a.*?\>]test111[^\<\/a\>]
                '<a href="">test111</a> ',
                $data['post_content']);

            /*$data['post_content'] = preg_replace_callback(
                '/[^>]test111[^<]/',
                array( &$this, 'tp_preg_replace'),
                $data['post_content']
            );
            /*if(strpos($title, 'origin') !== false){
                $data['post_content'] = str_replace(
                    'origin',
                    '<span data-title-case-origin-iata="'.$origin.'">'.$origin.'</span>' ,
                    $title);
            }*/
            //$data['post_content'] = str_replace(' '.'test111', ' <a href="test111">test111</a> ', $data['post_content']);
            /*$data['post_content'] = preg_replace(
                '/^test111$/',
                ' <a href="test111">test111</a>',
                $data['post_content']);

            error_log(print_r(preg_replace(
                '/^test111$/',
                ' <a href="test111">test111</a>',
                $data['post_content']), true));*/
        }
        //error_log(print_r($data, true));
        //error_log(print_r($postarr['tp_auto_replac_link'], true));
        return $data;
    }
    public function tp_preg_replace($matches) {
        /*error_log(print_r($matches, true));
        foreach($matches as $key=>$match){
            //$matches[$key] = $match.'11';
            error_log(print_r($match, true));
        }*/
        //$matches[0] = 't22';
        error_log(print_r($matches, true));
        return $matches[0];//.' 1 ';
    }

}