<?php


namespace Microweber\content\controllers;

use Microweber\View;

class Manager
{
    public $app = null;
    public $views_dir = 'views';
    public $provider = null;
    public $category_provider = null;
    public $event = null;

    function __construct($app = null)
    {
        if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = \Microweber\Application::getInstance();
            }
        }
        $this->views_dir = dirname(__DIR__) . DS . 'views' . DS;
        $this->provider = $this->app->content;
        $this->category_provider = $this->app->category;
        $this->event = $this->app->event;
        $is_admin = $this->app->user->admin_access();
    }

    function index($params)
    {
        if (isset($params['manage_categories'])) {
              print load_module('categories/manage',$params);

            return;


        }



        $no_page_edit = false;
        $posts_mod = array();
        // $posts_mod['type'] = 'content/admin_posts_list';
        if (isset($params['data-page-id'])) {
            $posts_mod['page-id'] = $params['data-page-id'];
        }

        if (isset($params['no_page_edit'])) {
        $no_page_edit = $params['no_page_edit'];
        }
        if (isset($params['keyword'])) {
            $posts_mod['search_by_keyword'] = $params['keyword'];
        }
        if (isset($params['content_type']) and $params['content_type'] != false) {
            $posts_mod['content_type'] = $params['content_type'];
        }
        if (isset($params['subtype']) and $params['subtype'] != false) {
            $posts_mod['subtype'] = $params['subtype'];
        }
        if (isset($params['is_shop']) and $params['is_shop'] == 'y') {
            $posts_mod['subtype'] = 'product';
        } else if (isset($params['is_shop']) and $params['is_shop'] == 'n') {
            $posts_mod['subtype'] = 'post';
        }
        if (isset($params['content_type_filter']) and $params['content_type_filter'] != '') {
            $posts_mod['content_type'] = $params['content_type_filter'];
        }
        if (isset($params['subtype_filter']) and $params['subtype_filter'] != '') {
            $posts_mod['subtype'] = $params['subtype_filter'];
        }


        if (!isset($params['category-id']) and isset($params['page-id']) and $params['page-id'] != 'global') {
            $check_if_exist = $this->provider->get_by_id($params['page-id']);
            if (is_array($check_if_exist)) {
                if (isset($check_if_exist['is_shop']) and trim($check_if_exist['is_shop']) == 'y') {
                    $posts_mod['subtype'] = 'product';
                }
            }
        }
        $page_info = false;
        if (isset($params['page-id'])) {
            if ($params['page-id'] == 'global') {
                if (isset($params['is_shop']) and $params['is_shop'] == 'y') {
                    $page_info = $this->provider->get('limit=1&one=1&content_type=page&is_shop=y');
                }
            } else {
                $page_info = $this->provider->get_by_id($params['page-id']);
            }
        }

        if (isset($params['category-id']) and $params['category-id'] != 'global') {
            $check_if_exist = $this->category_provider->get_page($params['category-id']);

            if (is_array($check_if_exist)) {
                $page_info = $check_if_exist;
                if (isset($check_if_exist['is_shop']) and trim($check_if_exist['is_shop']) == 'y') {
                    $posts_mod['subtype'] = 'product';
                }
            }
        }
        $posts_mod['paging_param'] = 'pg';
        $posts_mod['orderby'] = 'position desc';
        if (isset($posts_mod['page-id'])) {
            $posts_mod['parent'] = $posts_mod['page-id'];
        }
        if (isset($params['data-category-id'])) {
            $posts_mod['category-id'] = $params['data-category-id'];
        }
        if (isset($params['data-category-id'])) {
            $posts_mod['category-id'] = $params['data-category-id'];
        }
        if (isset($params[$posts_mod['paging_param']])) {
            $posts_mod['page'] = $params[$posts_mod['paging_param']];
        }
        if (isset($params['category-id'])) {
            $posts_mod['category'] = $params['category-id'];
        }
        $keyword = false;
        if (isset($posts_mod['search_by_keyword'])) {
            $keyword = strip_tags($posts_mod['search_by_keyword']);
        }

        $data = $this->provider->get($posts_mod);
        if (empty($data) and isset($posts_mod['page'])) {

            unset($posts_mod['page']);
            $data = $this->provider->get($posts_mod);

        }

        $post_params_paging = $posts_mod;
        $post_params_paging['page_count'] = true;
        $pages = $this->provider->get($post_params_paging);
        $this->event->emit('module.content.manager', $posts_mod);

        $post_toolbar_view = $this->views_dir . 'toolbar.php';

        $toolbar = new View($post_toolbar_view);
        $toolbar->assign('page_info', $page_info);
        $toolbar->assign('keyword', $keyword);
        $toolbar->assign('params', $params);


        $post_list_view = $this->views_dir . 'manager.php';
        if($no_page_edit == false){
        if ($data == false) {
            if (isset($page_info['content_type']) and $page_info['content_type'] == 'page' and $page_info['subtype'] == 'static') {
                $manager = new Edit();
                return $manager->index($params);
            }elseif (isset($page_info['content_type']) and $page_info['content_type'] == 'page' and isset($page_info['subtype']) 
			and isset($page_info['id']) 
			and $page_info['subtype'] != false 
			and $page_info['subtype'] != 'post' 
			and $page_info['subtype'] != 'static'  
			and $page_info['subtype'] != 'dynamic' 
			and $page_info['subtype'] != 'product' 
			and $page_info['subtype'] != 'page'

			 ) {
                    $manager = new Edit();
                    return $manager->index($params);


            }
        }
        }

        $view = new View($post_list_view);
        $view->assign('params', $params);
        $view->assign('page_info', $page_info);
        $view->assign('toolbar', $toolbar);
        $view->assign('data', $data);
        $view->assign('pages', $pages);
        $view->assign('keyword', $keyword);
        $view->assign('post_params', $posts_mod);
        $view->assign('paging_param', $posts_mod['paging_param']);
        return $view->display();
    }
}
