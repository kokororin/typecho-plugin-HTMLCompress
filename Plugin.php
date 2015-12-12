<?php
/**
 * HTMLCompress
 *
 * @package Typecho HTMLCompress Plugin
 * @author Kokororin
 * @original Steven Vachon(http://www.svachon.com/)
 * @version 1.1
 * @link https://kotori.love
 */

class HTMLCompress_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('HTMLCompress_Plugin', 'before');
    }

    /**
     * 禁用插件
     */
    public static function deactivate()
    {}

    /**
     * 插件设置
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {}

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function before($archive)
    {
        require_once dirname(__FILE__) . '/html-minify.php';
        ob_start('html_minify_buffer');
    }

}
