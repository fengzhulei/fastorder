<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：CategoryControoller.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：商品分类控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class CategoryController extends CommonController {

    private $cat_id = 0; // 分类id
    private $children = '';
    private $brand = 0; // 品牌
    private $type = ''; //商品类型
    private $price_min = 0; // 最低价格
    private $price_max = 0; // 最大价格
    private $ext = '';
    private $size = 10; // 每页数据
    private $page = 1; // 页数
    private $sort = 'last_update';
    private $order = 'ASC'; // 排序方式
    private $keywords = ''; // 搜索关键词
    private $filter_attr_str = 0;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->cat_id = I('request.id');
    }

    /**
     * 分类产品信息列表
     */
    public function index() {
        $this->parameter();
        /*if (I('get.id', 0) == 0 && CONTROLLER_NAME == 'category') {
            $arg = array(
                'id' => $this->cat_id,
                'brand' => $this->brand,
                'price_max' => $this->price_max,
                'price_min' => $this->price_min,
                'filter_attr' => $this->filter_attr_str,
                'page' => $this->page,
            );

            $url = url('Category/index', $arg);
            $this->redirect($url);
        }
        $this->assign('brand_id', $this->brand);
        $this->assign('price_max', $this->price_max);
        $this->assign('price_min', $this->price_min);
        $this->assign('filter_attr', $this->filter_attr_str);
        $this->assign('page', $this->page);
        $this->assign('size', $this->size);
        $this->assign('sort', $this->sort);
        $this->assign('order', $this->order);
        $this->assign('id', $this->cat_id);
        // 获取分类
        $this->assign('category', model('CategoryBase')->get_top_category());
        $count = model('Category')->category_get_count($this->children, $this->brand, $this->type, $this->price_min, $this->price_max, $this->ext);
*/
        $goodslist = $this->category_get_goods();
        $this->assign('goods_list', $goodslist);
       // $this->pageLimit(url('index', array('id' => $this->cat_id, 'brand' => $this->brand, 'price_max' => $this->price_max, 'price_min' => $this->price_min, 'filter_attr' => $this->filter_attr_str, 'sort' => $this->sort, 'order' => $this->order)), $this->size);
       // $this->assign('pager', $this->pageShow($count));

        /* 页面标题 */
       // $page_info = get_page_title($this->cat_id);
       // $this->assign('ur_here', $page_info['ur_here']);
        $this->assign('page_title', L('goods_list'));
        /*
        $cat = model('Category')->get_cat_info($this->cat_id);  // 获得分类的相关信息
        if (!empty($cat['keywords'])) {
            if (!empty($cat['keywords'])) {
                $this->assign('keywords_list', explode(' ', $cat['keywords']));
            }
        }
        $this->assign('categories', model('CategoryBase')->get_categories_tree($this->cat_id));
		*/
        $this->display('fastorder_goods.dwt');
    }

    /**
     * 异步加载商品列表
     */
    public function asynclist() {
        $this->parameter();
        $asyn_last = intval(I('post.last')) + 1;
        $this->size = I('post.amount');
        $this->page = ($asyn_last > 0) ? ceil($asyn_last / $this->size) : 1;
        $goodslist = $this->category_get_goods();
        foreach ($goodslist as $key => $goods) {
            $this->assign('goods', $goods);
            $sayList[] = array(
                'proeditbox' => ECTouch::view()->fetch('library/asynclist_info.lbi')
            );
        }
        die(json_encode($sayList));
        exit();
    }

    /**
     * 处理关键词
     */
    public function keywords() {
        $keywords = I('request.keywords');
        if ($keywords != '') {
            $this->keywords = 'AND (';
            $goods_ids = array();
            $val = mysql_like_quote(trim($keywords));
            $this->keywords .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' )";

            $sql = 'SELECT DISTINCT goods_id FROM ' . $this->model->pre . "tag WHERE tag_words LIKE '%$val%' ";
            $row = $this->model->query($sql);
            foreach ($row as $vo) {
                $goods_ids[] = $vo['goods_id'];
            }
            /**
             * 处理关键字查询次数
             */
            $sql = 'INSERT INTO ' . $this->model->pre . "keywords (date , searchengine,keyword ,count) VALUES ('" . local_date('Y-m-d') . "', '" . ECTouch . "', '" . addslashes(str_replace('%', '', $val)) . "', '1')";
            $condition = 'keyword = "' . addslashes(str_replace('%', '', $val)) . '"';
            $set = $this->model->table('keywords')
                    ->where($condition)
                    ->find();

            if (!empty($set)) {
                $sql .= ' ON DUPLICATE KEY UPDATE count = count+1';
            }
            $this->model->query($sql);
            $this->keywords .= ')';
            $goods_ids = array_unique($goods_ids);
            // 拼接商品id
            $tag_id = implode(',', $goods_ids);
            if (!empty($tag_id)) {
                $this->keywords .= 'OR g.goods_id ' . db_create_in($tag_id);
            }
            $this->assign('keywords', $keywords);
        }
    }

    /**
     * 处理参数便于搜索商品信息
     */
    private function parameter() {
    	
    	$page_size = C('page_size');
    	$this->size = intval($page_size) > 0 ? intval($page_size) : 10;
        $this->page = I('request.page') > 0 ? intval(I('request.page')) : 1;
        $type = $_GET['type'];
    	$type = (isset($type ) && in_array(trim(strtolower($type)), array('on_sale', 'not_on_sale', 'delete', 'all'))) ? trim(strtolower($type)) : 'all';
		$this->type =$type;
    	$this->assign('type', $type);
    	$this->assign('act','show_goods');
    	return ;
        
        // 如果分类ID为0，则返回总分类页
        if (empty($this->cat_id)) {
            $this->cat_id = 0;
        }
        // 获得分类的相关信息
        $cat = model('Category')->get_cat_info($this->cat_id);
        $this->keywords();
        $this->assign('show_asynclist', C('show_asynclist'));
        // 初始化分页信息
        $page_size = C('page_size');
        $brand = I('request.brand');
        $price_max = I('request.price_max');
        $price_min = I('request.price_min');
        $filter_attr = I('request.filter_attr');
        $this->size = intval($page_size) > 0 ? intval($page_size) : 10;
        $this->page = I('request.page') > 0 ? intval(I('request.page')) : 1;
        $this->brand = $brand > 0 ? $brand : 0;
        $this->price_max = $price_max > 0 ? $price_max : 0;
        $this->price_min = $price_min > 0 ? $price_min : 0;
        $this->filter_attr_str = $filter_attr > 0 ? $filter_attr : '0';

        $this->filter_attr_str = trim(urldecode($this->filter_attr_str));
        $this->filter_attr_str = preg_match('/^[\d\.]+$/', $this->filter_attr_str) ? $this->filter_attr_str : '';
        $filter_attr = empty($this->filter_attr_str) ? '' : explode('.', $this->filter_attr_str);

        /* 排序、显示方式以及类型 */
        $default_display_type = C('show_order_type') == '0' ? 'list' : (C('show_order_type') == '1' ? 'grid' : 'album');
        $default_sort_order_method = C('sort_order_method') == '0' ? 'DESC' : 'ASC';
        $default_sort_order_type = C('sort_order_type') == '0' ? 'goods_id' : (C('sort_order_type') == '1' ? 'shop_price' : 'last_update');
        $this->type = (isset($_REQUEST['type']) && in_array(trim(strtolower($_REQUEST['type'])), array('best', 'hot', 'new', 'promotion'))) ? trim(strtolower($_REQUEST['type'])) : '';
        $this->sort = (isset($_REQUEST['sort']) && in_array(trim(strtolower($_REQUEST['sort'])), array(
                    'goods_id',
                    'shop_price',
                    'last_update',
                    'click_count',
                    'sales_volume'
                ))) ? trim($_REQUEST['sort']) : $default_sort_order_type; // 增加按人气、按销量排序 by wang
        $this->order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array(
                    'ASC',
                    'DESC'
                ))) ? trim($_REQUEST['order']) : $default_sort_order_method;
        $display = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array(
                    'list',
                    'grid',
                    'album'
                ))) ? trim($_REQUEST['display']) : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
        $this->assign('display', $display);
        setcookie('ECS[display]', $display, gmtime() + 86400 * 7);
        $this->children = get_children($this->cat_id);
        /* 赋值固定内容 */
        if ($this->brand > 0) {
            $brand_name = model('Base')->model->table('brand')->field('brand_name')->where("brand_id = '$this->brand'")->getOne();
        } else {
            $brand_name = '';
        }

        /* 获取价格分级 */
        if ($cat['grade'] == 0 && $cat['parent_id'] != 0) {
            $cat['grade'] = model('Category')->get_parent_grade($this->cat_id); // 如果当前分类级别为空，取最近的上级分类
        }

        if ($cat['grade'] > 1) {
            /* 需要价格分级 */

            /*
              算法思路：
              1、当分级大于1时，进行价格分级
              2、取出该类下商品价格的最大值、最小值
              3、根据商品价格的最大值来计算商品价格的分级数量级：
              价格范围(不含最大值)    分级数量级
              0-0.1                   0.001
              0.1-1                   0.01
              1-10                    0.1
              10-100                  1
              100-1000                10
              1000-10000              100
              4、计算价格跨度：
              取整((最大值-最小值) / (价格分级数) / 数量级) * 数量级
              5、根据价格跨度计算价格范围区间
              6、查询数据库

              可能存在问题：
              1、
              由于价格跨度是由最大值、最小值计算出来的
              然后再通过价格跨度来确定显示时的价格范围区间
              所以可能会存在价格分级数量不正确的问题
              该问题没有证明
              2、
              当价格=最大值时，分级会多出来，已被证明存在
             */

            $sql = "SELECT min(g.shop_price) AS min, max(g.shop_price) as max " . " FROM " . $this->model->pre . 'goods' . " AS g " . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1  ';
            // 获得当前分类下商品价格的最大值、最小值

            $row = M()->getRow($sql);
            $row['max'] = $row['max'] ? $row['max'] : 0;
            $row['min'] = $row['min'] ? $row['min'] : 0;
            // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
            $price_grade = 0.0001;
            for ($i = - 2; $i <= log10($row['max']); $i++) {
                $price_grade *= 10;
            }

            // 跨度
            $dx = ceil(($row['max'] - $row['min']) / ($cat['grade']) / $price_grade) * $price_grade;
            if ($dx == 0) {
                $dx = $price_grade;
            }

            for ($i = 1; $row['min'] > $dx * $i; $i++)
                ;

            for ($j = 1; $row['min'] > $dx * ($i - 1) + $price_grade * $j; $j++)
                ;
            $row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);

            for (; $row['max'] >= $dx * $i; $i++)
                ;
            $row['max'] = $dx * ($i) + $price_grade * ($j - 1);

            $sql = "SELECT (FLOOR((g.shop_price - $row[min]) / $dx)) AS sn, COUNT(*) AS goods_num  " . " FROM " . $this->model->pre . 'goods' . " AS g " . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " GROUP BY sn ";

            $price_grade = $this->model->query($sql);

            foreach ($price_grade as $key => $val) {
                $temp_key = $key + 1;
                $price_grade[$temp_key]['goods_num'] = $val['goods_num'];
                $price_grade[$temp_key]['start'] = $row['min'] + round($dx * $val['sn']);
                $price_grade[$temp_key]['end'] = $row['min'] + round($dx * ($val['sn'] + 1));
                $price_grade[$temp_key]['price_range'] = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
                $price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
                $price_grade[$temp_key]['formated_end'] = price_format($price_grade[$temp_key]['end']);
                $price_grade[$temp_key]['url'] = url('category', array(
                    'cid' => $this->cat_id,
                    'bid' => $this->brand,
                    'price_min' => $price_grade[$temp_key]['start'],
                    'price_max' => $price_grade[$temp_key]['end'],
                    'filter_attr' => $filter_attr
                ));

                /* 判断价格区间是否被选中 */
                if (isset($_REQUEST['price_min']) && $price_grade[$temp_key]['start'] == $price_min && $price_grade[$temp_key]['end'] == $price_max) {
                    $price_grade[$temp_key]['selected'] = 1;
                } else {
                    $price_grade[$temp_key]['selected'] = 0;
                }
            }

            $price_grade[0]['start'] = 0;
            $price_grade[0]['end'] = 0;
            $price_grade[0]['price_range'] = L('all_attribute');
            $price_grade[0]['url'] = url('category/index', array(
                'cid' => $this->cat_id,
                'bid' => $brand,
                'price_min' => 0,
                'price_max' => 0,
                'filter_attr' => $filter_attr
            ));
            $price_grade[0]['selected'] = empty($price_max) ? 1 : 0;
            $this->assign('price_grade', $price_grade);
        }

        /* 品牌筛选 */

        $sql = "SELECT b.brand_id, b.brand_name, COUNT(*) AS goods_num " . "FROM " . $this->model->pre . 'brand' . " AS b, " . $this->model->pre . 'goods' . " AS g LEFT JOIN " . $this->model->pre . 'goods_cat' . " AS gc ON g.goods_id = gc.goods_id " . "WHERE g.brand_id = b.brand_id AND ($this->children OR " . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array(
                    $this->cat_id
                                        ), array_keys(cat_list($this->cat_id, 0, false))))) . ") AND b.is_show = 1 " . " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " . "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC";

        $brands = $this->model->query($sql);

        foreach ($brands as $key => $val) {
            $temp_key = $key + 1;
            $brands[$temp_key]['brand_id'] = $val['brand_id']; // 同步绑定品牌名称和品牌ID 
            $brands[$temp_key]['brand_name'] = $val['brand_name'];
            $brands[$temp_key]['url'] = url('category/index', array(
                'id' => $this->cat_id,
                'bid' => $val['brand_id'],
                'price_min' => $price_min,
                'price_max' => $price_max,
                'filter_attr' => $filter_attr
            ));

            /* 判断品牌是否被选中 */
            if ($brand == $val['brand_id']) {             // 修正当前品牌的ID
                $brands[$temp_key]['selected'] = 1;
            } else {
                $brands[$temp_key]['selected'] = 0;
            }
        }

        unset($brands[0]); // 清空索引为0的项目 
        $brands[0]['brand_id'] = 0; // 新增默认值
        $brands[0]['brand_name'] = L('all_attribute');
        $brands[0]['url'] = url('category', array(
            'cid' => $this->cat_id,
            'bid' => 0,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'filter_attr' => $filter_attr
        ));
        $brands[0]['selected'] = empty($brand) ? 1 : 0;

        ksort($brands);
        $this->assign('brands', $brands);
        /* 属性筛选 */
        $this->ext = ''; // 商品查询条件扩展
        if ($cat['filter_attr'] > 0) {
            $cat_filter_attr = explode(',', $cat['filter_attr']); // 提取出此分类的筛选属性
            $all_attr_list = array();
            foreach ($cat_filter_attr as $key => $value) {
                $sql = "SELECT a.attr_name FROM " . $this->model->pre . "attribute AS a, " . $this->model->pre . "goods_attr AS ga, " . $this->model->pre . "goods AS g WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ") AND a.attr_id = ga.attr_id AND g.goods_id = ga.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'";
                $res = $this->model->query($sql);
                if ($temp_name = $res[0]['attr_name']) {
                    $all_attr_list[$key]['filter_attr_id'] = $value; // 新增属性标识 by wang
                    $all_attr_list[$key]['filter_attr_name'] = $temp_name;

                    $sql = "SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value FROM " . $this->model->pre . "goods_attr AS a, " . $this->model->pre . "goods AS g" . " WHERE ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 ' . " AND a.attr_id='$value' " . " GROUP BY a.attr_value";

                    $attr_list = $this->model->query($sql);

                    $temp_arrt_url_arr = array();

                    for ($i = 0; $i < count($cat_filter_attr); $i++) { // 获取当前url中已选择属性的值，并保留在数组中
                        $temp_arrt_url_arr[$i] = !empty($filter_attr[$i]) ? $filter_attr[$i] : 0;
                    }
                    // “全部”的信息生成
                    $temp_arrt_url_arr[$key] = 0;
                    $temp_arrt_url = implode('.', $temp_arrt_url_arr);
                    // 默认数值
                    $all_attr_list[$key]['attr_list'][0]['attr_id'] = 0;
                    $all_attr_list[$key]['attr_list'][0]['attr_value'] = L('all_attribute');
                    $all_attr_list[$key]['attr_list'][0]['url'] = url('category/index', array(
                        'id' => $this->cat_id,
                        'bid' => $this->brand,
                        'price_min' => $this->price_min,
                        'price_max' => $this->price_max,
                        'filter_attr' => $temp_arrt_url
                    ));
                    $all_attr_list[$key]['attr_list'][0]['selected'] = empty($filter_attr[$key]) ? 1 : 0;

                    foreach ($attr_list as $k => $v) {
                        $temp_key = $k + 1;
                        // 为url中代表当前筛选属性的位置变量赋值,并生成以‘.’分隔的筛选属性字符串
                        $temp_arrt_url_arr[$key] = $v['goods_id'];
                        $temp_arrt_url = implode('.', $temp_arrt_url_arr);

                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_id'] = $v['goods_id']; // 新增属性参数 
                        $all_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $v['attr_value'];
                        $all_attr_list[$key]['attr_list'][$temp_key]['url'] = url('category/index', array(
                            'id' => $this->cat_id,
                            'bid' => $this->brand,
                            'price_min' => $this->price_min,
                            'price_max' => $this->price_max,
                            'filter_attr' => $temp_arrt_url
                        ));

                        if (!empty($filter_attr[$key]) and $filter_attr[$key] == $v['goods_id']) {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 1;
                        } else {
                            $all_attr_list[$key]['attr_list'][$temp_key]['selected'] = 0;
                        }
                    }
                }
            }
            $this->assign('filter_attr_list', $all_attr_list);
            // 扩展商品查询条件
            if (!empty($filter_attr)) {
                $ext_sql = "SELECT DISTINCT(b.goods_id) as dis FROM " . $this->model->pre . "goods_attr AS a, " . $this->model->pre . "goods_attr AS b " . "WHERE ";
                $ext_group_goods = array();
                // 查出符合所有筛选属性条件的商品id
                foreach ($filter_attr as $k => $v) {
                    unset($ext_group_goods);
                    if (is_numeric($v) && $v != 0 && isset($cat_filter_attr[$k])) {
                        $sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $cat_filter_attr[$k] . " AND a.goods_attr_id = " . $v;
                        $res = $this->model->query($sql);
                        foreach ($res as $value) {
                            $ext_group_goods[] = $value['dis'];
                        }
                        $this->ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
                    }
                }
            }
        }
    }

    /**
     * 获取分类信息
     * 获取顶级分类
     */
    public function top_all() {
        /* 页面的缓存ID */
        $cache_id = sprintf('%X', crc32($_SERVER['REQUEST_URI'] . C('lang')));
        if (!ECTouch::view()->is_cached('category_all.dwt', $cache_id)) {
            $category = model('CategoryBase')->get_categories_tree();
            $this->assign('title', L('catalog'));
            $this->assign('category', $category);
            /* 页面标题 */
            $page_info = get_page_title();
            $this->assign('ur_here', $page_info['ur_here']);
            $this->assign('page_title', L('catalog') . '_' . $page_info['title']);
        }
        $this->display('category_top_all.dwt', $cache_id);
    }

    /**
     * 获取分类信息
     * 只获取二级分类当没有参数时获取最高的二级分类
     */
    public function all() {
        $cat_id = I('get.id');
        /* 页面的缓存ID */
        $cache_id = sprintf('%X', crc32($_SERVER['REQUEST_URI'] . C('lang')));
        if (!ECTouch::view()->is_cached('category_all.dwt', $cache_id)) {
            // 获得请求的分类 ID
            if ($cat_id > 0) {
                $category = model('CategoryBase')->get_child_tree($cat_id);
            } else {
                //顶级分类
                ecs_header("Location: " . url('category/top_all') . "\n");
            }
            $this->assign('title', L('catalog'));
            $this->assign('category', $category);

            /* 页面标题 */
            $page_info = get_page_title($cat_id);
            $this->assign('ur_here', $page_info['ur_here']);
            $this->assign('page_title', ($cat_id > 0) ? $page_info['title'] : L('catalog') . '_' . $page_info['title']);
        }
        $this->display('category_all.dwt', $cache_id);
    }

    /**
     * 获得分类下的商品
     *
     * @access public
     * @param string $children            
     * @return array
     */
    private function category_get_goods() {

    	/*
    	$display = $GLOBALS['display'];
        $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " . "g.is_delete = 0 ";
        if ($this->keywords != '') {
            $where .= " AND (( 1 " . $this->keywords . " ) ) ";
        } else {
            $where.=" AND ($this->children OR " . model('Goods')->get_extension_goods($this->children) . ') ';
        }
        if ($this->type) {
            switch ($this->type) {
                case 'best':
                    $where .= ' AND g.is_best = 1';
                    break;
                case 'new':
                    $where .= ' AND g.is_new = 1';
                    break;
                case 'hot':
                    $where .= ' AND g.is_hot = 1';
                    break;
                case 'promotion':
                    $time = gmtime();
                    $where .= " AND g.promote_price > 0 AND g.promote_start_date <= '$time' AND g.promote_end_date >= '$time'";
                    break;
                default:
                    $where .= '';
            }
        }
        if ($this->brand > 0) {
            $where .= "AND g.brand_id=$this->brand ";
        }
        if ($this->price_min > 0) {
            $where .= " AND g.shop_price >= $this->price_min ";
        }
        if ($this->price_max > 0) {
            $where .= " AND g.shop_price <= $this->price_max ";
        }

        $start = ($this->page - 1) * $this->size;
        $sort = $this->sort == 'sales_volume' ? 'xl.sales_volume' : $this->sort;
        */
    	$where =" WHERE g.user_id = $_SESSION[user_id] ";
    	
    	if ($this->type) {
    		$type = $this->type;
            switch ($type) {
                case 'on_sale':
                    $where .= ' AND g.is_on_sale = 1';
                    break;
                case 'not_on_sale':
                    $where .= ' AND g.is_on_sale = 0';
                    break;
                case 'delete':
                    $where .= ' AND g.is_delete  = 1';
                    break;                
                default:
                    $where .= '';
            }
        }
    	$start = ($this->page - 1) * $this->size;
    	$where .=" order by g.goods_id desc  LIMIT $start , $this->size";
        /* 获得商品列表 */    	
    	
        $sql = 'SELECT g.goods_id, g.goods_name,  g.shop_price AS goods_price, g.goods_brief, g.is_on_sale FROM ' .
         $this->model->pre . "goods AS g ".
         $where;
        $res = $this->model->query($sql);
        $arr = array();
        foreach ($res as $row) {
            // 销量统计
           /* $sales_volume = (int) $row['sales_volume'];
            if (mt_rand(0, 3) == 3){
                $sales_volume = model('GoodsBase')->get_sales_count($row['goods_id']);
                $sql = 'REPLACE INTO ' . $this->model->pre . 'touch_goods(`goods_id`, `sales_volume`) VALUES('. $row['goods_id'] .', '.$sales_volume.')';
                $this->model->query($sql);
            }
            */
        	$sales_volume = model('GoodsBase')->get_sales_count($row['goods_id']);
        	
            $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
            if ($display == 'grid') {
                $arr[$row['goods_id']]['goods_name'] = C('goods_name_length') > 0 ? sub_str($row['goods_name'], C('goods_name_length')) : $row['goods_name'];
            } else {
                $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            }
            $arr[$row['goods_id']]['name'] = $row['goods_name'];
            $arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
            $arr[$row['goods_id']]['goods_price'] = $row['goods_price'];
             $arr[$row['goods_id']]['is_on_sale'] = $row['is_on_sale'] ;
            $arr[$row['goods_id']]['sales_count'] = $sales_volume;
           /*
            $arr[$row['goods_id']]['promotion'] = model('GoodsBase')->get_promotion_show($row['goods_id']);
            $arr[$row['goods_id']]['comment_count'] = model('Comment')->get_goods_comment($row['goods_id'], 0);  //商品总评论数量 
            $arr[$row['goods_id']]['favorable_count'] = model('Comment')->favorable_comment($row['goods_id'], 0);  //获得商品好评数量
           */  
        }
        return $arr;
    }

    /*
     * 编辑产品
     */
    public function edit_goods()
    {
    	// 初始化返回数组
        $result = array(
            'error' => 0,
            'message' => '',
            'content' => '',
            'goods_id' => ''
        );
    	

        if (empty($_POST ['goods_id'])) {
            $result ['error'] = 1;
            die(json_encode($result));
        }
        
        $json = new EcsJson;
        $goods = array(
		        'goods_id'=>$_POST ['goods_id'],
		        'goods_name'=>$_POST ['goods_name'],
		        'goods_price'=>$_POST ['goods_price'],
		        'goods_brief'=>$_POST ['goods_brief'],
		        'goods_status'=>$_POST ['goods_status']
        );
        
        if(!is_numeric($goods['goods_price']))
        {
        	 $result ['error'] = 1;
        	 $result ['message'] = '请输入正确的价格';
            die(json_encode($result));
        }
        
        //检查商品是否合法
       $sql= 'SELECT  g.goods_name,  g.shop_price AS goods_price, g.goods_brief, g.is_on_sale FROM ' . $this->model->pre . "goods AS g ". 
        " WHERE g.goods_id = $goods[goods_id] and g.user_id = $_SESSION[user_id] ";
       $res = $this->model->query($sql);
       
       $name_duplicate = $this->check_goods_name_duplicate($goods['goods_name'],$goods['goods_id']);
       if(empty($res))
       {
       		$result ['error'] = 1;
       		$result ['message'] = '该产品不存在';
            die(json_encode($result));
       }
       else if($name_duplicate)
       {
       		$result ['error'] = 1;
       		$result ['message'] = '该产品 名称已存在，请换一个名称';
            die(json_encode($result));
       }
       else
       {
       	  $sql = 'update ' . $this->model->pre . "goods set ".
       	         " goods_name = '$goods[goods_name]' ,".
		       	  " shop_price = $goods[goods_price] ,".
		       	  " goods_brief = '$goods[goods_brief]' ,".
		       	  " is_on_sale = $goods[goods_status] ".
		       	  " where goods_id = $goods[goods_id] and user_id = $_SESSION[user_id];";
       	  
       	  $this->model->query($sql);
       	  
       	  	$result ['error'] = 0;
       		$result ['message'] = '产品更新成功';
       		$result ['content'] = $json->encode($goods);
       		$result ['goods_id']=$goods[goods_id];
            die(json_encode($result));
       }
    }

    /*
     * 删除产品
     */
    public function  del_goods()
    {

    	$goods_id =I('request.goods_id');
     
    }
    
    /**
     * 增加一个产品
     * Enter description here ...
     */
    public function  add_goods()
    {
    	if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
    		$this->assign('act','add_goods');
    		$this->assign('title','增加产品');
    		$this->display('fastorder_goods.dwt');
    	}
    	else 
    	{
    		// 初始化返回数组
	        $result = array(
	            'error' => 0,
	            'message' => '',
	            'content' => '',
	            'goods_id' => ''
	        );
    		//新增产品
    		 $goods = array(
                'goods_name' => empty($_POST ['goods_name']) ? '' : I('post.goods_name'),
                'shop_price' => empty($_POST ['goods_price']) ? 0: intval($_POST ['goods_price']),
                'goods_brief' => empty($_POST ['goods_brief']) ? '' : I('post.goods_brief'),
                'is_on_sale' => empty($_POST ['goods_status']) ? 1 : intval($_POST ['goods_status'])
                );

             if(!is_numeric($goods['shop_price'])) 
             {
             	$result['error'] =1;
             	$result['message'] ='价格不正确';
             	die(json_encode($result));
             }  
             
             if($this->check_goods_name_duplicate($goods['goods_name']))
             {
             	$result['error'] =1;
             	$result['message'] ='已经有一个名叫'.$goods['goods_name'].'的产品，请换一个名称';
             	die(json_encode($result));
             }
             
             $sql = 'insert into '. $this->model->pre . "goods ".
                    "(goods_name,shop_price,goods_brief,is_on_sale,user_id)".
		             " values ".
		             "('$goods[goods_name]',$goods[goods_price],'$goods[goods_brief]',$goods[goods_price],$_SESSION[user_id]);";

             $goods['user_id'] = $_SESSION['user_id'];
       	  	 $goods_id = model('Goods')->add_goods($goods);
           	 $result['message'] ='产品增加成功';
           	 $result['goods_id'] =$goods_id;
           	 $result['content'] =$goods;
             die(json_encode($result));
    	}
    	
    	
    }
     
    /*
     * 检查产品名称是否重复，重复返回true
     */
    private  function check_goods_name_duplicate($goods_name,$goods_id = 0)
    {
    	$count = model('Goods')->check_goods_name_count($goods_name,$goods_id);
    	return ($count > 0)? true:false;
    }
    
    /**
     * 筛选产品，为生成链接做准备
     * Enter description here ...
     */
    public function goods_select()
    {
    	$goods_list = model('Goods')->get_user_goods_list();
    	
    	$this->assign('goods_list',$goods_list);
    	$this->assign('act','goods_select');
    	$this->assign('title','选择产品');
    	$this->display('goods_show.dwt');
    }
    
    public function generate_link()
    {    	
    	$goods_str =rtrim($_POST['goods_ids'],',');
    	//$goods_str = trim($goods_str,'\"');
    	//$goods_str = rtrim($goods_str,',');
    	$goods_ids =explode(',', $goods_str) ;
    	$user_id = $_SESSION['user_id'];
    	
    	$params['goods_ids'] =$goods_ids;
    	$params['user_id'] = $user_id;
    	    	
    	$base64 = urlsafe_b64encode(serialize($params));
    	$url = __URL__ . '/index.php?m=default&c=Category&a=goods_show&data=' . $base64;
    	die($url);
    }
    
	/**
     * 显示已经生成的链接
     * Enter description here ...
     */
    public function goods_show()
    {
    	$data = I('get.data');
    	$data = unserialize(urlsafe_b64decode($data));
    	
    	$goods_list = model('Goods')->get_user_goods_list_byId($data['goods_ids'],$data['user_id']);
    	
    	$this->assign('goods_list',$goods_list);
    	$this->assign('user_id',$data['user_id']);
    	$this->assign('act','goods_show');
    	$this->assign('title','购买产品');
    	$this->display('goods_show.dwt');
    }
}
