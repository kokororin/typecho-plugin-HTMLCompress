<?php
/**
 * HTML压缩插件
 *
 * @package HTMLCompress
 * @author Kokororin
 * @version 1.3
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
    {
    }

    /**
     * 插件设置
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function before($archive)
    {
        ob_start(array('HTMLCompress_Plugin', 'parser'));
    }

    public static function parser($html)
    {
        require_once __DIR__ . '/vendor/autoload.php';
        $parser = \WyriHaximus\HtmlCompress\Factory::construct();
        return $parser->compress($html);
    }

}
