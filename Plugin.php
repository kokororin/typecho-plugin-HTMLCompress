<?php
/**
 * HTMLCompress
 *
 * @package Typecho HTMLCompress Plugin
 * @author Kokororin
 * @original Steven Vachon(http://www.svachon.com/)
 * @version 1.2
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
        $compress_css = new Typecho_Widget_Helper_Form_Element_Radio(
            'compress_css', array(
                'yes' => '压缩',
                'no' => '不压缩',
            ), '', '是否压缩css', '默认是，建议启用');
        $form->addInput($compress_css);
        $compress_js = new Typecho_Widget_Helper_Form_Element_Radio(
            'compress_js', array(
                'yes' => '压缩',
                'no' => '不压缩',
            ), '', '是否压缩js', '默认否，非常不建议启用，很容易出错');
        $form->addInput($compress_js);
        $info_comment = new Typecho_Widget_Helper_Form_Element_Radio(
            'info_comment', array(
                'yes' => '启用',
                'no' => '不启用',
            ), '', '是否在页尾插入压缩比例信息', '默认否');
        $form->addInput($info_comment);
        $remove_comments = new Typecho_Widget_Helper_Form_Element_Radio(
            'remove_comments', array(
                'yes' => '启用',
                'no' => '不启用',
            ), '', '是否移除注释', '默认是');
        $form->addInput($remove_comments);
        $shorten_urls = new Typecho_Widget_Helper_Form_Element_Radio(
            'shorten_urls', array(
                'yes' => '启用',
                'no' => '不启用',
            ), '', '是否缩短url', '默认是');
        $form->addInput($shorten_urls);

    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function before($archive)
    {
        require_once dirname(__FILE__) . '/html-minify.php';
        ob_start('html_minify_buffer');
    }

}
