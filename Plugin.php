<?php
/**
 * Simditor Editor
 *
 * @package Simditor
 * @author NickVico
 * @version 1.0.0
 * @link http://www.nickvico.com
 */
class Simditor_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 *
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function activate()
	{
		Typecho_Plugin::factory('admin/write-post.php')->richEditor = array('Simditor_Plugin', 'render');
		Typecho_Plugin::factory('admin/write-page.php')->richEditor = array('Simditor_Plugin', 'render');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->content = array('Simditor_Plugin', 'filter');
		Typecho_Plugin::factory('Widget_Archive')->header = array('Simditor_Plugin', 'header');
		Typecho_Plugin::factory('Widget_Archive')->footer = array('Simditor_Plugin', 'footer');
	}

	/**
	 * 禁用插件方法,如果禁用失败,直接抛出异常
	 *
	 * @static
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function deactivate(){}

	/**
	 * 获取插件配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form 配置面板
	 * @return void
	 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		echo '<p style="font-size:16px;text-align:center;">感谢您使用 Typecho 编辑器插件 :<font color="#4A89DC"> Simditor Editor</font><font color="#F40"> 1.0.0 </font>   『 <a href="//github.com/nickvico/simditor-for-typecho" target="_blank">帮助与更新</a> 』</p>';

		$codeHighlight = new Typecho_Widget_Helper_Form_Element_Radio('codeHighlight', array(1 => _t('启用'), 0 => _t('禁用')),
		1, _t('代码高亮'), _t('如果你不希望在前台模版中启用本插件自带代码高亮库请禁用此项。'));
		$form->addInput($codeHighlight);

		$showCodeLine = new Typecho_Widget_Helper_Form_Element_Radio('showCodeLine', array(1 => _t('启用'), 0 => _t('禁用')),
		1, _t('显示代码行号'), _t('代码高亮时是否在左侧显示代码行号，本项只有当启用代码高亮时才生效。'));
		$form->addInput($showCodeLine);

		$elCodeHighlight = new Typecho_Widget_Helper_Form_Element_Radio('elCodeHighlight', array(1 => _t('启用'), 0 => _t('禁用')),
		0, _t('易代码高亮'), _t('是否启用易语言代码高亮，此项仅在开启代码高亮选项后有效，需引入 jQuery 库。'));
		$form->addInput($elCodeHighlight);

		$jQueryLoadMode = new Typecho_Widget_Helper_Form_Element_Radio('jQueryLoadMode', array(1 => _t('启用自动加载'), 0 => _t('禁用自动加载')),
		0, _t('jQuery加载方式'), _t('如果你需要本插件自动加载 jQuery 库则选择 启用自动加载。'));
		$form->addInput($jQueryLoadMode);

		$moreTitle = new Typecho_Widget_Helper_Form_Element_Text('moreTitle', NULL, _t('- 阅读剩余部分 -'), _t('阅读更多'), _t('首页、分类、归档页 输出摘要时 <b>more</b> 标签显示的标题内容。'));
		$form->addInput($moreTitle);
	}

	/**
	 * 个人用户的配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	/**
	 * 输出头部css
	 *
	 * @access public
	 * @param unknown $header
	 * @return unknown
	 */
	public static function header() {
		$plugin_options = Typecho_Widget::widget('Widget_Options')->plugin('Simditor');
		$codeHighlight = $plugin_options->codeHighlight;
		if ( $codeHighlight ) {
			$editorPath = Typecho_Common::url('Simditor/editor', Helper::options()->pluginUrl);
			echo '<link rel="stylesheet" href="' . $editorPath . '/prism/prism.min.css" />';
			$elCodeHighlight = $plugin_options->elCodeHighlight;
			if ( $elCodeHighlight ) {
				echo '<link rel="stylesheet" href="' . $editorPath . '/prism/ecode.min.css" />';
			}
		}
	}

	/**
	 * 输出尾部js
	 *
	 * @access public
	 * @param unknown $header
	 * @return unknown
	 */
	public static function footer() {
		$plugin_options = Typecho_Widget::widget('Widget_Options')->plugin('Simditor');
		$editorPath = Typecho_Common::url('Simditor/editor', Helper::options()->pluginUrl);

		$jQueryLoadMode = $plugin_options->jQueryLoadMode;
		if ( $jQueryLoadMode ) {
			echo '<script src="' . $editorPath . '/scripts/jquery.min.js"></script>';
		}

		$codeHighlight = $plugin_options->codeHighlight;
		if ( $codeHighlight ) {
			echo '<script src="' . $editorPath . '/prism/prism.min.js"></script>';
			$elCodeHighlight = $plugin_options->elCodeHighlight;
			if ( $elCodeHighlight ) {
				echo '<script src="' . $editorPath . '/prism/ecode.min.js"></script>';
			}
		}
	}

	/**
	 * 过滤 more 标记
	 *
	 * @access public
	 * @return string
	 */
	public static function filter($content, Widget_Archive $archive)
	{
		$plugin_options = Typecho_Widget::widget('Widget_Options')->plugin('Simditor');
		$codeHighlight = $plugin_options->codeHighlight;
		$showCodeLine = $plugin_options->showCodeLine;
		if ( $codeHighlight && $showCodeLine ) {
			$content = str_replace('<pre>', '<pre class="line-numbers">', $content);
		}
		if ($archive->is('index') || $archive->is('archive')) {
			if (strpos($content, '—————More—————')) {
				$archive->text = str_replace('—————More—————', '<!--more-->', $content);
				return $archive->excerpt . "<p class=\"more\"><a href=\"{$archive->permalink}#more\" title=\"{$archive->title}\">{$plugin_options->moreTitle}</a></p>";
			}
			return $content;
		}
		return str_replace(array('<!--more-->', '—————More—————'), '', $content);
	}

	/**
	 * 插件实现方法
	 *
	 * @access public
	 * @return void
	 */
	public static function render($post)
	{
		$editorPath = Typecho_Common::url('Simditor/editor', Helper::options()->pluginUrl);
		$SimInitCode = <<<nickvico

<style>
.simditor-body {
	padding: 10px 15px 10px !important;
}
.simditor-body p, h1, h2, h3, h4, h5, ol, ul, table {
	margin-top: 0 !important;
	margin-bottom: 0 !important;
}
.category-option ul, .allow-option ul {
    margin-top: 14px !important;
}
.toolbar-item-video {
	display: none !important;
}
</style>

<link rel="stylesheet" href="{$editorPath}/styles/simditor.css" />
<link rel="stylesheet" href="{$editorPath}/styles/simditor-prettyemoji.css" />
<link rel="stylesheet" href="{$editorPath}/styles/simditor-html.css" />
<link rel="stylesheet" href="{$editorPath}/styles/simditor-checklist.css" />
<link rel="stylesheet" href="{$editorPath}/styles/simditor-fullscreen.css" />
<link rel="stylesheet" href="{$editorPath}/styles/simditor-video.css" />

<script type="text/javascript" src="{$editorPath}/scripts/module.min.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/hotkeys.min.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor.min.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-mark.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-prettyemoji.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/beautify-html.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-html.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-checklist.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-fullscreen.js"></script>
<script type="text/javascript" src="{$editorPath}/scripts/simditor-video.js"></script>

<script type="text/javascript">var simditorPath = "{$editorPath}";</script>
<script type="text/javascript" src="{$editorPath}/init.min.js"></script>

nickvico;
		echo $SimInitCode;
	}

}
