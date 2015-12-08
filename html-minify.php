<?php
/*
HTML Minify 0.5.7 <http://www.svachon.com/blog/html-minify/>
Reduce file size by shortening URLs and safely removing all standard comments and unnecessary white space from an HTML document.
*/

class HTML_Minify
{
	// Settings
	protected $compress_css;
	protected $compress_js;
	protected $info_comment;
	protected $remove_comments;
	protected $shorten_urls;
	
	// Variables
	protected $html = '';
	
	
	
	public function __construct($html, $compress_css=true, $compress_js=false, $info_comment=false, $remove_comments=true, $shorten_urls=true)
	{
		if ($html !== '')
		{
			$this->compress_css = $compress_css;
			$this->compress_js = $compress_js;
			$this->info_comment = $info_comment;
			$this->remove_comments = $remove_comments;
			$this->shorten_urls = $shorten_urls;
			
			$this->html = $this->minifyHTML($html);
			
			if ($this->info_comment)
			{
				$this->html .= "\n" . $this->bottomComment($html, $this->html);
			}
		}
	}
	
	
	
	public function __toString()
	{
		return $this->html;
	}
	
	
	
	protected function bottomComment($raw, $compressed)
	{
		$raw = strlen($raw);
		$compressed = strlen($compressed);
		
		$savings = ($raw-$compressed) / $raw * 100;
		
		$savings = round($savings, 2);
		
		return '<!--Bytes before:'.$raw.', after:'.$compressed.'; saved:'.$savings.'%-->';
	}
	
	
	
	protected function callback_HTML_URLs($matches)
	{
		// [2] is an attribute value that is encapsulated with "" and [3] with ''
		$url = (!isset($matches[3])) ? $matches[2] : $matches[3];
		
		return $matches[1].'="'.absolute_to_relative_url($url).'"';
	}
	
	
	
	protected function minifyHTML($html)
	{
		$pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
		
		if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) === false)
		{
			// Invalid markup
			return $html;
		}
		
		$overriding = false;
		$raw_tag = false;
		
		// Variable reused for output
		$html = '';
		
		foreach ($matches as $token)
		{
			$tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
			
			$content = $token[0];
			
			$relate = false;
			$strip = false;
			
			if (is_null($tag))
			{
				if ( !empty($token['script']) )
				{
					$strip = $this->compress_js;
					
					// Will still end up shortening URLs within the script, but should be OK..
					// Gets Shortened:   test.href="http://domain.com/wp"+"-content";
					// Gets Bypassed:    test.href = "http://domain.com/wp"+"-content";
					$relate = $this->compress_js;
				}
				else if ( !empty($token['style']) )
				{
					$strip = $this->compress_css;
					
					// No sense in trying to relate at this point because currently only URLs within HTML attributes are shortened
					//$relate = $this->compress_css;
				}
				else if ($content === '<!--wp-html-compression no compression-->')
				{
					$overriding = !$overriding;
					
					// Don't print the comment
					continue;
				}
				else if ($this->remove_comments)
				{
					if (!$overriding && $raw_tag !== 'textarea')
					{
						// Remove any HTML comments, except MSIE conditional comments
						$content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
						
						$relate = true;
						$strip = true;
					}
				}
			}
			else	// All tags except script, style and comments
			{
				if ($tag === 'pre' || $tag === 'textarea')
				{
					$raw_tag = $tag;
				}
				else if ($tag === '/pre' || $tag === '/textarea')
				{
					$raw_tag = false;
				}
				else if (!$raw_tag && !$overriding)
				{
					if ($tag !== '')
					{
						if (strpos($tag, '/') === false)
						{
							// Remove any empty attributes, except:
							// action, alt, content, src
							$content = preg_replace('/(\s+)(\w++(?<!action|alt|content|src)=(""|\'\'))/i', '$1', $content);
						}
						
						// Remove any space before the end of a tag (including closing tags and self-closing tags)
						$content = preg_replace('/\s+(\/?\>)/', '$1', $content);
						
						// Do not shorten canonical URL
						if ($tag !== 'link')
						{
							$relate = true;
						}
						else if (preg_match('/rel=(?:\'|\")\s*canonical\s*(?:\'|\")/i', $content) === 0)
						{
							$relate = true;
						}
					}
					else	// Content between opening and closing tags
					{
						// Avoid multiple spaces by checking previous character in output HTML
						if (strrpos($html,' ') === strlen($html)-1)
						{
							// Remove white space at the content beginning
							$content = preg_replace('/^[\s\r\n]+/', '', $content);
						}
					}
					
					$strip = true;
				}
			}
			
			// Relate URLs
			if ($relate && $this->shorten_urls)
			{
				$content = preg_replace_callback('/(action|data|href|src)=(?:"([^"]*)"|\'([^\']*)\')/i', array(&$this,'callback_HTML_URLs'), $content);
			}
			
			if ($strip)
			{
				$content = $this->removeWhiteSpace($content, $html);
			}
			
			$html .= $content;
		}
		
		return $html;
	}
	
	
	
	protected function removeWhiteSpace($html, $full_html)
	{
		$html = str_replace("\t", ' ', $html);
		$html = str_replace("\r", ' ', $html);
		$html = str_replace("\n", ' ', $html);
		
		// This is over twice the speed of a RegExp
		while (strpos($html, '  ') !== false)
		{
			$html = str_replace('  ', ' ', $html);
		}
		
		return $html;
	}
}



function html_minify_buffer($html)
{
	// Duplicate library may be in use elsewhere
	if (!function_exists('absolute_to_relative_url'))
	{
		require_once dirname(__FILE__) . '/absolute-to-relative-urls.php';
	}
	
	return new HTML_Minify($html);
}



//ob_start('html_minify_buffer');



?>