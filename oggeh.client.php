<?php
	/*
	 * OGGEH HTML Parser
	 * @version 0.8
	 * 
	 * Author: Ahmed Abbas - OGGEH Cloud Computing LLC - oggeh.com
	 * License: GNU-GPL v3 (http://www.gnu.org/licenses/gpl.html)
	 * -------------------------------------------------------------------
	 * Copyright (C) 2002-2017 Ahmed Abbas - OGGEH Cloud Computing LLC - oggeh.com
	 * 
	 * OGGEH HTML Parser is free software: you can redistribute it and/or modify it
	 * under the terms of the GNU General Public License as
	 * published by the Free Software Foundation, either version 3 of the
	 * License, or (at your option) any later version.
	 * 
	 * OGGEH HTML Parser is distributed in the hope that it will be useful, but
	 * WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	 * See the GNU General Public License for more details.
	 * 
	 * You should have received a copy of the License
	 * along with OGGEH HTML Parser. If not, see
	 * <https://github.com/oggeh/html-parser/LICENSE.txt>.
	 * 
	 * See LICENSE.txt file for more information.
	 * -------------------------------------------------------------------
	 *
	 */
	class OGGEH {
		/*
		 * SandBox Mode.
		 *
		 * @var string
		 */
		static $sandbox = false;
		/*
		 * Rewrite Enabled.
		 *
		 * @var string
		 */
		static $rewrite = false;
		/*
		 * App Domain.
		 *
		 * @var string
		 */
		static $domain = '';
		/*
		 * App API Key.
		 *
		 * @var string
		 */
		static $api_key = '';
		/*
		 * App API Secret.
		 *
		 * @var string
		 */
		static $sandbox_key = '';
		/*
		 * Selected language.
		 *
		 * @var string
		 */
		static $app_lang = 'en';
		/*
		 * Templates directory.
		 *
		 * @var string
		 */
		static $tpl_dir = 'tpl';
		/*
		 * Unlock users.
		 *
		 * @var string
		 */
		static $unlock_users = array(
			array(
				'user'=>'admin',
				'pass'=>'admin'
			)
		);
		/*
		 * User Dictionary.
		 *
		 * @var string
		 */
		static $i18n = array();
		/*
		 * URL language code.
		 *
		 * @var string
		 */
		public $url_lang = 'en';
		/*
		 * URL module name.
		 *
		 * @var string
		 */
		public $url_module = '';
		/*
		 * URL child module id.
		 *
		 * @var string
		 */
		public $url_child_id = '';
		/*
		 * URL child extra id.
		 *
		 * @var string
		 */
		public $url_extra_id = '';
		/*
		 * Unlock modules.
		 *
		 * @var string
		 */
		static $locked_modules = array();
		/*
		 * Default image blank source.
		 *
		 * @var string
		 */
		static $blank = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNui8sowAAAAWdEVYdENyZWF0aW9uIFRpbWUAMTAvMjUvMTcYFuBOAAAADUlEQVQImWP4//8/AwAI/AL+hc2rNAAAAABJRU5ErkJggg==';
		/*
		 * App object.
		 *
		 * @var string
		 */
		public $app = null;
		/*
		 * App URI.
		 *
		 * @var string
		 */
		private $uri = '';
		/*
		 * API Endpoint.
		 *
		 * @var string
		 */
		private $endpoint = 'https://api.oggeh.com';
		/*
		 * App published.
		 *
		 * @var boolean
		 */
		private $published = true;
		/*
		 * App direction.
		 *
		 * @var string
		 */
		private $direction = 'ltr';
		/*
		 * RTL languages.
		 *
		 * @var string
		 */
		private $rtl_langs = array('ar', 'fa');
		/*
		 * Locale list.
		 *
		 * @var array
		 */
		private $locale = array();
		/*
		 * OGGEH Client class constructor.
		 *
		 */
		function __construct() {
			date_default_timezone_set('Africa/Cairo');
			session_start();
			error_reporting(0);
			if (is_file('locale.json')) {
				$this->locale = array_reverse(json_decode(file_get_contents('locale.json'), true));
			}
			$this->uri = $_SERVER['REQUEST_URI'];
			$pieces = parse_url($this->uri);
			$url_lang_set = false;
			if (self::$rewrite) {
				$path = trim($pieces['path'], '/');
				$segments = explode('/', $path);
				if (count($segments)>0) {
					if (strlen($segments[0])>0) {
						$this->url_lang = $segments[0];
						$url_lang_set = true;
						$this->direction = (in_array($this->url_lang, $this->rtl_langs)) ? 'rtl' : 'ltr';
					}
					if (count($segments)>1) {
						$this->url_module = $segments[1];
						if (count($segments)>2) {
							$this->url_child_id = urldecode($segments[2]);
							if (count($segments)>3) {
								$this->url_extra_id = urldecode($segments[3]);
							}
						}
					}
				}
			} else {
				parse_str($pieces['query'], $query);
				if (isset($query['lang'])) {
					$this->url_lang = $query['lang'];
					$url_lang_set = true;
					$this->direction = (in_array($this->url_lang, $this->rtl_langs)) ? 'rtl' : 'ltr';
				}
				if (isset($query['module'])) {
					$this->url_module = $query['module'];
				}
				if (isset($query['param1'])) {
					$this->url_child_id = $query['param1'];
				}
				if (isset($query['param2'])) {
					$this->url_extra_id = $query['param2'];
				}
			}
			if ($this->url_module != '' && !is_file(self::$tpl_dir.'/'.$this->url_module.'.html') || strlen($this->url_lang) != 2) {
				$this->url_module = '404';
			} elseif ($this->url_module == 'index' || $this->url_module == '') {
				$this->url_module = 'home';
			}
			$this->app = $this->call(array(
				array(
      		'method'=>'get.app'
      	)
      ));
      if (is_string($this->app)) {
      	echo $this->app;
      	exit;
      } else {
      	$this->app = $this->app[0]['output'];
      	if (!$url_lang_set && $this->app && $this->app['default_lang'] != 'en') {
      		if (self::$rewrite) {
      			header('Location: /'.$this->app['default_lang']);
      		} else {
      			header('Location: /?lang='.$this->app['default_lang']);
      		}
      		exit;
      	}
	      $this->published = ($this->app) ? true : false;
	      if (!$this->published) {
	      	$this->url_module = 'inactive';
	      }
      }
		}
		/*
		 * Configure the settings of OGGEH Client.
		 *
		 */
		static function configure($setting, $value=null) {
			if (is_array($setting)) {
				foreach ($setting as $key=>$value) {
					self::configure($key, $value);
				}
			} elseif (property_exists(__CLASS__, $setting)) {
				self::$$setting = $value;
			}
		}
		protected function utf8ize($mixed) {
      $mixed = (is_object($mixed)) ? (array)$mixed : $mixed;
      if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
          $mixed[$key] = $this->utf8ize($value);
        }
      } else if (is_string($mixed)) {
        return utf8_encode($mixed);
      }
      return $mixed;
    }
    public function jsonEncode($input, $pretty=false) {
      if ($pretty) {
        $encoded = json_encode($input, JSON_PRETTY_PRINT);
      } else {
        $encoded = json_encode($input);
      }
      switch (json_last_error()) {
        case JSON_ERROR_NONE:
        return $encoded;
        break;
        case JSON_ERROR_DEPTH:
        return '[Maximum stack depth exceeded]';
        break;
        case JSON_ERROR_STATE_MISMATCH:
        return '[Underflow or the modes mismatch]';
        break;
        case JSON_ERROR_CTRL_CHAR:
        return '[Unexpected control character found]';
        break;
        case JSON_ERROR_SYNTAX:
        return '[Syntax error, malformed JSON]';
        break;
        case JSON_ERROR_UTF8:
        $clean = $this->utf8ize($input);
        return $this->jsonEncode($clean, $pretty);
        break;
        default:
        return '';
        break;
      }
    }
		/*
		 * Make an API call.
		 * @param array $vars various key-value pairs
		 * @return object
		 */
		public function call($data=array()) {
			$sandbox = self::$sandbox;
			$domain = self::$domain;
			$api_key = self::$api_key;
			$sandbox_key = self::$sandbox_key;
			$lang = $this->url_lang;
			if (!isset($api_key) || empty($api_key)) {
				echo '[missing api key!]';
				exit;
			}
			$vars = array(
				'api_key'=>$api_key,
				'lang'=>$lang
			);
			$query = '';
			foreach($vars as $key=>$value) {
	      if (is_array($value)) {
	        for ($i=0; $i<count($value); $i++) {
	          $query .= $key.'='.$value[$i].'&';
	        }
	      } else {
	        $query .= $key.'='.$value.'&';
	      }
	    }
	    $query = rtrim($query, '&');
	    if (!isset($this->endpoint)) {
	    	echo '[missing endpoint!]';
				exit;
	    }
	    $url = $this->endpoint;
	    if ($query != '') {
	      $url .= '/?'.$query;
	    }
	    $body = $this->jsonEncode($data);
	    $cookie = sys_get_temp_dir().'oggeh';
	    $res = '';
	    try {
	    	//error_log($url);
	    	//error_log($body);
	    	$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'OGGEH v1.0');
		    $headers = array(
		    	'Origin: '.$domain,
		    	'Accept: application/json',
		    	'Content-Type: application/json',
		    	'Content-Length: '.strlen($body)
		    );
		    if ($sandbox) {
		    	$headers[] = 'SandBox: '.hash_hmac('sha512', $domain.$api_key, $sandbox_key);
		    }
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, true);
      	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 360);
		    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		    $res = curl_exec($ch);
		    if ($res === false) {
		    	$res = '['.curl_error($ch).']';
		    	curl_close($ch);
		    	exit;
		    } else {
		    	curl_close($ch);
		    	$res = json_decode($res, true);
			    if ($res) {
			    	if ($res['error'] != '') {
			    		if ($res['error'] != 'app not published' && $res['error'] != 'account suspended') {
			    			$res = '['.$res['error'].']';
			    		}
				    } else {
				    	$res = $res['stack'];
				    }
			    } else {
			    	$res = '['.$res.']';
			    }
		    }
	    } catch (Exception $e) {
	    	$res = '['.$e->getMessage().']';
	    }
	    return $res;
		}
		/*
		 * Parse oggeh html tags.
		 * @param string $tpl location of target html document
		 * @return string
		 */
		public function parse($tpls) {
			$output = '';
			$oggeh = array();
			$stack = array();
			$elements = array();
			if (!is_array($tpls)) {
				$tpls = array($tpls);
			}
			foreach ($tpls as $tpl) {
				$tpl = file_get_contents($tpl);
				$tpl = preg_replace('#(?: {2,}|[\r\n\t]+)#s', '$1', $tpl);
				$tpl = preg_replace('/<!--(.*)-->/Uis', '', $tpl);
				$tpl = $this->setURLParams($tpl);
				$output .= $tpl;
				preg_match_all('@<(?P<tag>oggeh)(?P<options>\s[^>]+)?\s*?/>@xsi', $tpl, $self_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE); // self-closing tags
				preg_match_all('@<(?P<tag>oggeh)(?P<options>\s[^/>]+)?\s*?>(?P<content>.*?)</oggeh>@xsi', $tpl, $nonself_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE); // non-self-closing tags
				$tags = array_merge($self_tags, $nonself_tags);
				foreach ($tags as $tag) {
					$options = array();
					if (!empty($tag['options'][0])) {
						if (preg_match_all('@(?P<name>[^\s].*?)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi', $tag['options'][0], $attrs, PREG_SET_ORDER)) {
							foreach($attrs as $attr){
								if (!empty($attr['value_quoted'])){
									$value = $attr['value_quoted'];
			          } else if (!empty($attr['value_unquoted'])){
									$value = $attr['value_unquoted'];
			          } else {
									$value = '';
			          }
			          $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
			          $options[str_replace('-', '_', $attr['name'])] = $value;
							}
						}
					}
					$content = '';
					if (isset($tag['content'])) {
						$content = $tag['content'][0];
					}
					$element = $tag[0][0];
					if (!in_array($element, $elements)) {
						$elements[] = $element;
						$stack[] = $options;
						$oggeh[] = array(
							'tpl'=>$tpl,
							'elm'=>$element,
							'opts'=>$options,
							'innr'=>$content,
							'self'=>(($content != '') ? true : false)
						);
					}
				}
			}
			$output = preg_replace('#(?: {2,}|[\r\n\t]+)#s', '$1', $output);
			if (count($stack) > 0) {
				$res = $this->call($stack);
				if (is_array($res)) {
					for ($i=0; $i<count($res); $i++) {
						$replace = $this->render((object)$oggeh[$i]['opts'], $oggeh[$i]['innr'], $res[$i]);
						$output = str_replace($oggeh[$i]['elm'], $replace, $output);
					}
				}
				$output = $this->setMatchedClass($output);
			}
			return $output;
		}
		/*
		 * Translate a phrase
		 * @param string $phrase
		 * @return string
		 */
		protected function translate($phrase) {
			$translation = $phrase;
			if (isset(self::$i18n[$phrase])) {
				if (isset(self::$i18n[$phrase][$this->url_lang])) {
					$translation = self::$i18n[$phrase][$this->url_lang];
				}
			}
			return $translation;
		}
		/*
		 * Drill tag selection
		 * @param string $html
		 * @param object $obj base selection object from api response
		 * @return string
		 */
		protected function select($html, $obj) {
			$replace = '';
			//error_log(json_encode($obj));
			preg_match_all('/{\$(?P<var>.*?)}/', $html, $find, PREG_SET_ORDER, 0); // variable tags
			if ($find) {
				foreach ($find as $v) {
					//error_log($v['var']);
					if (stristr($v['var'], '.')) {
						$replace = $obj;
						$drill = explode('.', $v['var']);
						foreach ($drill as $d) {
							$d = (is_numeric($d)) ? (int)$d : $d;
							$replace = $replace[$d];
							if ($d == 'url' && $replace == '' && isset(self::$blank)) {
								$replace = self::$blank;
							}
						}
					} else {
						if (isset($obj[$v['var']])) {
							$replace = $obj[$v['var']];
						} else {
							$replace = $obj; // handle plain values (identified by $ only)
							if (stristr($html, '{$flag}')) {
								$flag = $this->getCountryCodeByLang($replace);
								$html = preg_replace('/{\$flag}/', $flag, $html);
							}
						}
						if ($v['var'] == 'url' && $replace == '' && isset(self::$blank)) {
							$replace = self::$blank;
						}
					}
					$replace = str_replace('/watch?v=', '/embed/', $replace); // proper youtube iframe embedding!
					$html = str_replace('{$'.$v['var'].'}', $replace, $html);
				}
			}
			return $html;
		}
		/*
		 * Iterate selection on custom attribute
		 * @param object $obj base selection object from api response
		 * @param object/string $iterate target loop property
		 * @param integer $index iteration cycle
		 * @return object
		 */
		protected function iterate($obj, $iterate, $index) {
			$case = '';
			if (is_array($obj)) {
				if (array_keys($obj) !== range(0, count($obj) - 1)) {
					$case = 'object';
				} else {
					$case = 'array';
				}
			}
			if (is_array($iterate)) {
				if ($iterate[$index] != '$') {
					switch ($case) {
						case 'object':
						foreach ($obj as $k=>$v) {
							if ($k == $iterate[$index]) {
								$obj = $v;
							}
						}
						break;
						case 'array':
						foreach ($obj as $o) {
							if (array_keys($o) !== range(0, count($o) - 1)) {
								foreach ($o as $k=>$v) {
									if ($k == $iterate[$index]) {
										$obj = $v;
									}
								}
							} else {
								$obj = '[unable to capture iteratation on key `'.$iterate[$index].'`]';
							}
						}
						break;
						default:
						$obj = '[unable to iterate on static output]';
						break;
					}
				}
				if (is_array($obj) && $index < count($iterate)-1) {
					$obj = $this->iterate($obj, $iterate, $index+1);
				}
			} else {
				switch ($case) {
					case 'object':
					$obj = $obj[$iterate];
					break;
					case 'array':
					foreach ($obj as $o) {
						if (array_keys($o) !== range(0, count($o) - 1)) {
							foreach ($o as $k=>$v) {
								if ($k == $iterate) {
									$obj = $v;
								}
							}
						} else {
							$obj = '[unable to capture iteratation on key `'.$iterate.'`]';
						}
					}
					break;
					default:
					$obj = '[unable to iterate on static output]';
					break;
				}
			}
			return $obj;
		}
		/*
		 * Render repeated and/or nested tags
		 * @param string $html
		 * @param string $repeat html snippet for repeating
		 * @param string $nest html snippet for nesting
		 * @param object $obj from api response
		 * @param string $select target property
		 * @param string $iterate target loop property
		 * @param boolean $convert optional setting to treating one element array as an associative array
		 * @return string
		 */
		protected function repeat($html, $repeat, $nest, $obj, $select=null, $iterate=null, $convert=true) {
			$select = (stristr($select, ',')) ? explode(',', $select) : $select;
			if ($html != '') {
				if (isset($obj[$select]) && is_string($select)) {
					$obj = $obj[$select];
				}
				if (!empty($iterate)) {
					$iterate = (stristr($iterate, '.')) ? explode('.', $iterate) : $iterate;
					$obj = $this->iterate($obj, $iterate, 0);
					$iterate = (is_array($iterate)) ? implode('.', $iterate) : $iterate;
				}
				$is_model = $select == 'model' || $iterate == 'model';
				$is_media = !$nest && $select == 'media';
				$is_files = !$nest && $select == 'files';
				$is_album = !$nest && $iterate == 'items';
				if ($convert && !$is_model && !$is_media && !$is_files && !$is_album) {
					$obj = (is_array($obj) && count($obj) == 1 && array_keys($obj) === range(0, count($obj) - 1)) ? $obj[0] : $obj; // treating one element array as an associative array (after above opertions)
				}
				$case = '';
				if (is_array($obj)) {
					if (array_keys($obj) !== range(0, count($obj) - 1)) {
						$case = 'object';
					} else {
						$case = 'array';
					}
				}
				switch ($case) {
					case 'object':
					if ($repeat) {
						$items = '';
						foreach ($obj as $key=>$value) {
							$item = $repeat[0]['innr'];
							if (stristr($item, '{$'.$select.'.key}') || stristr($item, '{$'.$select.'.key|translate}')) {
								if (stristr($item, '{$'.$select.'.key|translate}')) {
									$item = str_replace('{$'.$select.'.key|translate}', $this->translate($key), $item);
								} else {
									$item = str_replace('{$'.$select.'.key}', $key, $item);
								}
							}
							$value = ($key == 'price') ? $value.$this->app['currency'] : $value;
							if (stristr($item, '{$'.$select.'.value}')) {
								$item = str_replace('{$'.$select.'.value}', $value, $item);
							}
							if (stristr($item, '{$'.$key.'}')) {
								$item = str_replace('{$'.$key.'}', $value, $item);
							}
							if (!stristr($item, '{$')) { // bypass unwanted properties
								$items .= str_replace($repeat[0]['innr'], $item, $repeat[0][0]);
							}
						}
						$html = str_replace($repeat[0][0], $items, $html);
						// capture variables on repeatable wrapper as well
						foreach ($obj as $key=>$value) {
							if (stristr($html, '{$'.$select.'.key}') || stristr($html, '{$'.$select.'.key|translate}')) {
								if (stristr($html, '{$'.$select.'.key|translate}')) {
									$html = str_replace('{$'.$select.'.key|translate}', $this->translate($key), $html);
								} else {
									$html = str_replace('{$'.$select.'.key}', $key, $html);
								}
							}
							$value = ($key == 'price') ? $value.$this->app['currency'] : $value;
							if (stristr($html, '{$'.$select.'.value}')) {
								$html = str_replace('{$'.$select.'.value}', $value, $html);
							}
							if (stristr($html, '{$'.$key.'}')) {
								$html = str_replace('{$'.$key.'}', $value, $html);
							}
						}
						if (stristr($html, '{$')) { // remove unwanted properties
							$html = preg_replace('#\{.*?\}#', '', $html);
						}
					} else {
						$html = $this->select($html, $obj);
					}
					break;
					case 'array':
					if ($repeat) {
						$items = '';
						foreach ($obj as $o) {
							if (count($o['childs']) > 0 && $nest) {
								$item = $nest[0]['innr'];
							} else {
								$item = $repeat[0]['innr'];
							}
							$item = $this->select($item, $o);
							if (count($o['childs']) > 0 && $nest) {
								$nr = str_replace('{$oggeh-clone-repeat}', $repeat[0][0], $nest[0][0]);
								$nr = $this->repeat($nr, $repeat, $nest, $o['childs'], $select, $iterate, false);
								$nr = $this->select($nr, $o);
								$items .= $nr;
							} else {
								$items .= str_replace($repeat[0]['innr'], $item, $repeat[0][0]);
							}
						}
						$html = str_replace($repeat[0][0], $items, $html);
						if ($nest) {
							$html = str_replace($nest[0][0], '', $html);
						}
					} else {
						$html = $this->select($html, $obj);
					}
					break;
					default:
					if ($is_model) {
						$html = ''; // return nothing when there's no model attributes
					} else {
						if (stristr($html, '{$value}')) {
							$html = str_replace('{$value}', $obj, $html);
						} else {
							$html = $obj;
						}
					}
					break;
				}
			} else {
				$case = '';
				if (is_array($obj)) {
					if (array_keys($obj) !== range(0, count($obj) - 1)) {
						$case = 'object';
					} else {
						$case = 'array';
					}
				}
				switch ($case) {
					case 'object':
					if (is_string($select)) {
						$html = $obj[$select];
					} else {
						return '[invalid select, please use only one attribute on single output]';
					}
					break;
					case 'array':
					return '[missing repeatable markup]';
					break;
					default:
					if (stristr($html, '{$value}')) {
						$html = str_replace('{$value}', $obj, $html);
					} else {
						$html = $obj;
					}
					break;
				}
			}
			$html = str_replace('/watch?v=', '/embed/', $html); // proper youtube iframe embedding!
			return $html;
		}
		/*
		 * Render form fields html output
		 * @param string $fields html snippet for form fields
		 * @param object $obj from api response
		 * @return string
		 */
		protected function form($fields, $obj) {
			$tpls = array();
			if ($fields) {
				if (array_keys($fields) !== range(0, count($fields) - 1)) {
					$fields = array($fields);
				}
				foreach ($fields as $field) {
					preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?/>@xsi', $field[0], $self_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
					preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?>(?P<content>.*?)<\/\1>@xsi', $field[0], $nonself_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
					$tags = array_merge($self_tags, $nonself_tags);
					if ($tags) {
						foreach ($tags as $tag) {
							$options = array();
							if (!empty($tag['options'][0])) {
								if (preg_match_all('@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi', $tag['options'][0], $attrs, PREG_SET_ORDER)) {
									foreach($attrs as $attr){
										if (!empty($attr['value_quoted'])){
											$value = $attr['value_quoted'];
					          } else if (!empty($attr['value_unquoted'])){
											$value = $attr['value_unquoted'];
					          } else {
											$value = '';
					          }
					          $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
					          $options[str_replace('-', '_', $attr['name'])] = $value;
									}
								}
							}
							$content = '';
							if (isset($tag['content'])) {
								$content = $tag['content'][0];
							}
							$element = $tag[0][0];
							$tpls[] = array(
								'elm'=>$element,
								'opts'=>$options,
								'innr'=>$content
							);
						}
					}
				}
			}
			$items = '';
			foreach ($obj as $o) {
				$item = $this->buildField($o, $tpls);
				$items .= $item;
			}
			return $items;
		}
		/*
		 * Build single form field html output.
		 * @param object $obj from api response
		 * @param string $tpls field options from inner html
		 * @return string
		 */
		protected function buildField($obj, $tpls) {
			$type = (isset($obj['subtype'])) ? $obj['subtype'] : '';
			$required = (isset($obj['required'])) ? ' required' : '';
			$multiple = (isset($obj['multiple'])) ? ' multiple' : '';
			$field = '';
			switch ($obj['type']) {
				case 'text':
				$field .= $this->renderField($obj, $tpls, '<input name="'.$obj['name'].'" type="'.$type.'"'.$required.'>');
				break;
				case 'textarea':
				$field .= $this->renderField($obj, $tpls, '<textarea name="'.$obj['name'].'"'.$required.'></textarea>');
				break;
				case 'select':
				$field .= '<select name="'.$obj['name'].'"'.$required.$multiple.'>';
				foreach ($obj['options'] as $option) {
					$field .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
				}
				$field .= '</select>';
				$field = $this->renderField($obj, $tpls, $field);
				break;
				case 'radio-group':
				foreach ($obj['options'] as $option) {
					$field .= $this->renderField($obj, $tpls, '<input name="'.$obj['name'].'" type="radio" value="'.$option['value'].'"'.$required.'>', $option['label']);
				}
				break;
				case 'checkbox-group':
				foreach ($obj['options'] as $option) {
					$field .= $this->renderField($obj, $tpls, '<input name="'.$obj['name'].'[]" type="checkbox" value="'.$option['value'].'"'.$required.'>', $option['label']);
				}
				break;
				case 'header':
				case 'paragraph':
				$field .= $this->renderField($obj, $tpls, '<'.$type.'>'.$obj['label'].'</'.$type.'>');
				break;
				case 'hr':
				$field .= $this->renderField($obj, $tpls, '<hr>');
				break;
				default:
				$field .= $this->renderField($obj, $tpls, '<input name="'.$obj['name'].'" type="'.$obj['type'].'"'.$required.'>');
				break;
			}
			return $field;
		}
		/*
		 * Render single form field html output.
		 * @param object $obj from api response
		 * @param string $tpls field options from inner html
		 * @param string $field field default control markup
		 * @param string $label field default label text
		 * @return string
		 */
		protected function renderField($obj, $tpls, $field, $label=null) {
			$html = '';
			$unsafe = array(
				'inject',
				'name',
				'label',
				'type',
				'subtype',
				'required',
				'multiple',
				'toggle'
			);
			$nolabel = array(
				'header',
				'paragraph',
				'hr'
			);
			$markup = null;
			foreach ($tpls as $tpl) {
				if (isset($tpl['opts']['type']) && $tpl['opts']['type'] == $obj['type']) {
					if (!isset($tpl['opts']['subtype']) || $tpl['opts']['subtype'] == $obj['subtype']) {
						$markup = $tpl;
					}
					break;
				}
			}
			if (!$markup) {
				foreach ($tpls as $tpl) {
					if (!isset($tpl['opts']['type'])) {
						$markup = $tpl;
						break;
					}
				}
			}
			if ($markup) {
				$del = '';
				foreach ($markup['opts'] as $key=>$value) {
					if (in_array($key, $unsafe)) {
						$del .= ' '.$key.'="'.$value.'"';
					}
				}
				$html = str_replace($del, '', $markup['elm']);
				if (isset($markup['opts']['inject']) && !empty($markup['opts']['inject'])) { // element inject attribute
					if (stristr($markup['opts']['inject'], '|')) {
						$cond = explode('|', $markup['opts']['inject']);
						if (isset($obj[$cond[1]]) && !empty($obj[$cond[1]]) && (string)$obj[$cond[1]] == 'true') {
							$field = str_replace(' name="', ' class="'.$cond[0].'" name="', $field);
						}
					} else {
						$field = str_replace(' name="', ' class="'.$markup['opts']['inject'].'" name="', $field);
					}
				}
				preg_match_all('/<(?P<tag>\w+)[^>]*\s(?:inject)="(?P<inject>.*?)"[^>]*>/', $html, $inject, PREG_SET_ORDER, 0); // inner inject attribute
				if ($inject) {
					foreach ($inject as $inj) {
						$tag = $inj[0];
						preg_match_all('/\s(?:class)="(?P<class>.*?)"/', $tag, $class, PREG_SET_ORDER, 0); // inner inject attribute
						if (stristr($inj['inject'], '|')) {
							$cond = explode('|', $inj['inject']);
							if (isset($obj[$cond[1]]) && !empty($obj[$cond[1]]) && (string)$obj[$cond[1]] == 'true') {
								if ($class) {
									$tag = str_replace(' class="'.$class[0]['class'].'"', ' class="'.$class[0]['class'].' '.$cond[0].'"', $tag);
								} else {
									$tag = str_replace('<'.$inj['tag'].' ', '<'.$inj['tag'].' class="'.$cond[0].'"', $tag);
								}
							}
						} else {
							if ($class) {
								$tag = str_replace(' class="'.$class[0]['class'].'"', ' class="'.$class[0]['class'].' '.$inj['inject'].'"', $tag);
							} else {
								$tag = str_replace('<'.$inj['tag'].' ', '<'.$inj['tag'].' class="'.$inj['inject'].'"', $tag);
							}
						}
						$html = str_replace($inj[0], $tag, $html);
					}
				}
				if (stristr($html, '{$label}')) {
					if (!in_array($obj['type'], $nolabel)) {
						if (isset($label)) {
							$html = str_replace('{$label}', $label, $html);
						} elseif (isset($obj['label'])) {
							$html = str_replace('{$label}', $obj['label'], $html);
						}
					} else {
						$html = str_replace('{$label}', '', $html);
					}
				}
				if (stristr($html, '{$name}')) {
					$html = str_replace('{$name}', $obj['name'].((isset($obj['options']) && $obj['type'] == 'checkbox-group') ? '[]' : ''), $html);
				}
				if (stristr($html, '{$control}')) {
					$html = str_replace('{$control}', $field, $html);
				}
			}
			if ($html == '') {
				$html .= '<div class="field">';
				if (!in_array($obj['type'], $nolabel)) {
					if (isset($label)) {
						$html .= '<label for="'.$obj['name'].((isset($obj['options']) && $obj['type'] == 'checkbox-group') ? '[]' : '').'">'.$label.'</label>';
					} elseif (isset($obj['label'])) {
						$html .= '<label for="'.$obj['name'].((isset($obj['options']) && $obj['type'] == 'checkbox-group') ? '[]' : '').'">'.$obj['label'].'</label>';
					}
				}
				$html .= $field;
				$html .= '</div>';
			}
			return $html;
		}
		/*
		 * Render search results html output
		 * @param string $snippets html snippet for search results
		 * @param object $obj from api response
		 * @return string
		 */
		protected function search($snippets, $obj) {
			$tpls = array();
			if (array_keys($snippets) !== range(0, count($snippets) - 1)) {
				$snippets = array($snippets);
			}
			foreach ($snippets as $snippet) {
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?/>@xsi', $snippet[0], $self_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?>(?P<content>.*?)<\/\1>@xsi', $snippet[0], $nonself_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				$tags = array_merge($self_tags, $nonself_tags);
				if ($tags) {
					foreach ($tags as $tag) {
						$options = array();
						if (!empty($tag['options'][0])) {
							if (preg_match_all('@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi', $tag['options'][0], $attrs, PREG_SET_ORDER)) {
								foreach($attrs as $attr){
									if (!empty($attr['value_quoted'])){
										$value = $attr['value_quoted'];
				          } else if (!empty($attr['value_unquoted'])){
										$value = $attr['value_unquoted'];
				          } else {
										$value = '';
				          }
				          $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
				          $options[str_replace('-', '_', $attr['name'])] = $value;
								}
							}
						}
						$content = '';
						if (isset($tag['content'])) {
							$content = $tag['content'][0];
						}
						$element = $tag[0][0];
						$tpls[] = array(
							'elm'=>$element,
							'opts'=>$options,
							'innr'=>$content
						);
					}
				}
			}
			$items = '';
			if (count($obj) > 0) {
				foreach ($obj as $o) {
					$item = $this->renderSearch($o, $tpls);
					$items .= $item;
				}
			} else {
				$items = $this->renderSearch(array('target'=>'none','items'=>array()), $tpls);
			}
			return $items;
		}
		/*
		 * Render single search result html output
		 * @param object $obj from api response
		 * @param string $tpls html snippet for search results
		 * @return string
		 */
		protected function renderSearch($obj, $tpls) {
			$unsafe = array(
				'target'
			);
			$markup = null;
			foreach ($tpls as $tpl) {
				if (isset($tpl['opts']['target']) && $tpl['opts']['target'] == $obj['target']) {
					$markup = $tpl;
					break;
				}
			}
			if ($markup) {
				$del = '';
				foreach ($markup['opts'] as $key=>$value) {
					if (in_array($key, $unsafe)) {
						$del .= ' '.$key.'="'.$value.'"';
					}
				}
				$html = str_replace($del, '', $markup['elm']);
				$innr = $markup['innr'];
				$target = $obj['target'];
				foreach ($obj['items'] as $item) {
					switch ($target) {
						case 'pages':
						$innr = str_replace('{$key}', $item['key'], $innr);
						$innr = str_replace('{$subject}', $item['subject'], $innr);
						$innr = str_replace('{$header}', $item['header'], $innr);
						break;
						case 'news':
						$innr = str_replace('{$timestamp}', $item['timestamp'], $innr);
						$innr = str_replace('{$subject}', $item['subject'], $innr);
						$innr = str_replace('{$header}', $item['header'], $innr);
						break;
						case 'albums':
						$innr = str_replace('{$label}', $item['label'], $innr);
						break;
						case 'contacts':
						$innr = str_replace('{$name}', $item['name'], $innr);
						$innr = str_replace('{$email}', $item['email'], $innr);
						break;
						case 'locations':
						$innr = str_replace('{$address}', $item['address'], $innr);
						$innr = str_replace('{$latitude}', $item['latitude'], $innr);
						$innr = str_replace('{$longitude}', $item['longitude'], $innr);
						break;
					}
				}
				$html = str_replace($markup['innr'], $innr, $html);
			}
			return $html;
		}
		/*
		 * Render page blocks html output
		 * @param string $snippets html snippet for page blocks
		 * @param object $obj from api response
		 * @return string
		 */
		protected function blocks($snippets, $obj) {
			$tpls = array();
			if (array_keys($snippets) !== range(0, count($snippets) - 1)) {
				$snippets = array($snippets);
			}
			foreach ($snippets as $snippet) {
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?/>@xsi', $snippet[0], $self_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?>(?P<content>.*?)<\/\1>@xsi', $snippet[0], $nonself_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				$tags = array_merge($self_tags, $nonself_tags);
				if ($tags) {
					foreach ($tags as $tag) {
						$options = array();
						if (!empty($tag['options'][0])) {
							if (preg_match_all('@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi', $tag['options'][0], $attrs, PREG_SET_ORDER)) {
								foreach($attrs as $attr){
									if (!empty($attr['value_quoted'])){
										$value = $attr['value_quoted'];
				          } else if (!empty($attr['value_unquoted'])){
										$value = $attr['value_unquoted'];
				          } else {
										$value = '';
				          }
				          $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
				          $options[str_replace('-', '_', $attr['name'])] = $value;
								}
							}
						}
						$content = '';
						if (isset($tag['content'])) {
							$content = $tag['content'][0];
						}
						$element = $tag[0][0];
						$tpls[] = array(
							'elm'=>$element,
							'opts'=>$options,
							'innr'=>$content
						);
					}
				}
			}
			$items = '';
			foreach ($obj as $o) {
				$item = $this->renderBlock($o, $tpls);
				$items .= $item;
			}
			return $items;
		}
		/*
		 * Render single page block html output
		 * @param object $obj from api response
		 * @param string $tpls html snippet for page blocks
		 * @return string
		 */
		protected function renderBlock($obj, $tpls) {
			$unsafe = array(
				'type',
				'filter'
			);
			$types = array(
				'rte',
				'media',
				'files',
				'table'
			);
			$filters = array(
				'photo',
				'audio',
				'video'
			);
			$markup = null;
			foreach ($tpls as $tpl) {
				if (isset($tpl['opts']['type']) && $tpl['opts']['type'] == $obj['type']) {
					if (!isset($tpl['opts']['filter']) || isset($obj[$obj['type']]) && $obj[$obj['type']][0]['type'] == $tpl['opts']['filter']) {
						$markup = $tpl;
						break;
					}
				}
			}
			if ($markup) {
				$del = '';
				foreach ($markup['opts'] as $key=>$value) {
					if (in_array($key, $unsafe)) {
						$del .= ' '.$key.'="'.$value.'"';
					}
				}
				$html = str_replace($del, '', $markup['elm']);
				preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-repeat[^>]*>(?P<innr>.*?)<\/\1>/', $html, $repeat, PREG_SET_ORDER, 0); // repeatable tags
				$type = $obj['type'];
				switch ($type) {
					case 'rte':
					foreach ($obj as &$o) {
						$o = preg_replace('/<style(.*?)<\/style>/', '', $o);
						$o = strip_tags($o, '<p><b><strong><i><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>');
						$o = preg_replace('/\sstyle=\"(.*?)\"/', '', $o);
						$o = preg_replace('/\sdir=\"(.*?)\"/', '', $o);
					}
					$innr = $this->repeat($markup['innr'], $repeat, null, $obj);
					break;
					case 'table':
					$innr = '';
					$prefix = ($this->url_lang != 'en') ? '_'.$this->url_lang : '';
					foreach ($obj[$type.$prefix] as $index => $row) {
						if ($index == 0) {
							$innr .= '<thead>';
						} elseif ($index == 1) {
							$innr .= '<tbody>';
						}
						$innr .= '<tr>';
						foreach ($row as $col) {
							if ($index == 0) {
								$innr .= '<th>'.$col.'</th>';
							} else {
								$innr .= '<td>'.$col.'</td>';
							}
						}
						$innr .= '</tr>';
						if ($index == 0) {
							$innr .= '</thead>';
						} elseif ($index == count($obj[$type])-1) {
							$innr .= '</tbody>';
						}
					}
					$innr = str_replace('{$table}', $innr, $markup['innr']);
					break;
					default:
					$innr = $this->repeat($markup['innr'], $repeat, null, $obj, $type);
					break;
				}
				$html = str_replace($markup['innr'], $innr, $html);
				if (isset($obj['size_x']) && !empty($obj['size_x'])) {
					$html = preg_replace('/{\$size_x}/', $obj['size_x'], $html);
				}
				if (isset($obj['size_y']) && !empty($obj['size_y'])) {
					$html = preg_replace('/{\$size_y}/', $obj['size_y'], $html);
				}
				if (isset($obj['row']) && !empty($obj['row'])) {
					$html = preg_replace('/{\$row}/', $obj['row'], $html);
				}
				if (isset($obj['row']) && !empty($obj['row'])) {
					$html = preg_replace('/{\$row}/', $obj['row'], $html);
				}
			}
			return $html;
		}
		/*
		 * Render page albums html output
		 * @param string $snippets html snippet for album blocks
		 * @param object $obj from api response
		 * @return string
		 */
		protected function album($albums, $obj) {
			$tpls = array();
			if (array_keys($albums) !== range(0, count($albums) - 1)) {
				$albums = array($albums);
			}
			foreach ($albums as $album) {
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?/>@xsi', $album[0], $self_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				preg_match_all('@<(?P<tag>.*?)(?P<options>\s[^/>]+)?\s*?>(?P<content>.*?)<\/\1>@xsi', $album[0], $nonself_tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				$tags = array_merge($self_tags, $nonself_tags);
				if ($tags) {
					foreach ($tags as $tag) {
						$options = array();
						if (!empty($tag['options'][0])) {
							if (preg_match_all('@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi', $tag['options'][0], $attrs, PREG_SET_ORDER)) {
								foreach($attrs as $attr){
									if (!empty($attr['value_quoted'])){
										$value = $attr['value_quoted'];
				          } else if (!empty($attr['value_unquoted'])){
										$value = $attr['value_unquoted'];
				          } else {
										$value = '';
				          }
				          $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
				          $options[str_replace('-', '_', $attr['name'])] = $value;
								}
							}
						}
						$content = '';
						if (isset($tag['content'])) {
							$content = $tag['content'][0];
						}
						$element = $tag[0][0];
						$tpls[] = array(
							'elm'=>$element,
							'opts'=>$options,
							'innr'=>$content
						);
					}
				}
			}
			$items = '';
			foreach ($obj as $o) {
				$item = $this->renderAlbum($o, $tpls);
				$items .= $item;
			}
			return $items;
		}
		/*
		 * Render single album html output
		 * @param object $obj from api response
		 * @param string $tpls html snippet for album blocks
		 * @return string
		 */
		protected function renderAlbum($obj, $tpls) {
			$unsafe = array(
				'media'
			);
			$media = array(
				'photo',
				'audio',
				'video',
				'file'
			);
			$markup = null;
			foreach ($tpls as $tpl) {
				if (isset($tpl['opts']['media']) && $tpl['opts']['media'] == $obj['media']) {
					$markup = $tpl;
					break;
				}
			}
			if ($markup) {
				$del = '';
				foreach ($markup['opts'] as $key=>$value) {
					if (in_array($key, $unsafe)) {
						$del .= ' '.$key.'="'.$value.'"';
					}
				}
				$html = str_replace($del, '', $markup['elm']);
				$html = $this->select($html, $obj);
				$html = str_replace($markup['innr'], $innr, $html);
			}
			return $html;
		}
		/*
		 * Render oggeh html output.
		 * @param string $opts tag options
		 * @param string $innr non-self-closing tag inner html
		 * @param object $output api response
		 * @return string
		 */
		protected function render($opts, $innr, $output) {
			// NOTE: invalid response might break html head tag!
			$output = $output['output'];
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-repeat[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $repeat, PREG_SET_ORDER, 0); // repeatable tags
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-nest[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $nest, PREG_SET_ORDER, 0); // nested tags
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-field[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $fields, PREG_SET_ORDER, 0); // form field tags
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-static[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $statics, PREG_SET_ORDER, 0); // form static tags
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-snippet[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $snippets, PREG_SET_ORDER, 0); // inner templates
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-album[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $albums, PREG_SET_ORDER, 0); // inner templates
			preg_match_all('/<(?P<tag>\w+)[^>]*oggeh-search[^>]*>(?P<innr>.*?)<\/\1>/', $innr, $search, PREG_SET_ORDER, 0); // inner search results templates
			$html = '';
			switch ($opts->method) {
				case 'get.app':
				if (stristr($opts->select, '.')) {
					$value =  explode('.', $opts->select);
					$html = '<'.$value[0].' name="'.$value[1].'" content="'.$this->app[$value[0]][$value[1]].'">';
				} else {
					$html = $this->repeat($innr, $repeat, $nest, $this->app, $opts->select, $opts->iterate);
				}
				break;
				case 'get.search.results':
				if ($search) {
					$html .= $this->search($search, $output);
					if ($statics) {
						foreach ($statics as $stc) {
							$html .= $stc[0];
						}
					}
				} else {
					$html = '[unable to find marckup for search results]';
				}
				break;
				case 'get.albums':
				if ($albums) {
					$html .= $this->album($albums, $output);
				} else {
					$html = '[unable to find marckup for albums]';
				}
				break;
				default:
				$convert = false;
				if (!in_array($opts->method, array('get.pages','get.locations','get.contacts')) || $opts->method == 'get.news' && $opts->limit > 1) {
					$convert = true;
					$output = (is_array($output) && count($output) == 1 && array_keys($output) === range(0, count($output) - 1)) ? $output[0] : $output; // treating one element array as an associative array
				}
				if (count($output)) {
					if ($opts->select == 'blocks') {
						if ($opts->block_type == 'form' && $opts->iterate == 'form') {
							// render form fields
							if (count($output[$opts->select]) > 0) {
								if (isset($output[$opts->select][0][$opts->iterate])) {
									preg_match_all('@<form(?P<options>\s[^>]+)?\s*?>(?P<content>.*?)</form>@xsi', $innr, $form, PREG_SET_ORDER | PREG_OFFSET_CAPTURE); // inner form tag
									if ($form) {
										$html = '<form'.$form[0]['options'][0].'>';
										$html .= '<input type="hidden" name="method" value="post.page.form">';
										$html .= '<input type="hidden" name="key" value="'.$opts->key.'">';
										$html .= '<input type="hidden" name="token" value="'.$output[$opts->select][0]['token'].'">';
										$output = $this->iterate($output[$opts->select], $opts->iterate, 0);
										$html .= $this->form($fields, $output);
										if ($statics) {
											foreach ($statics as $stc) {
												$html .= $stc[0];
											}
										}
										$html .= '</form>';
									} else {
										$html = '[unable to find form tag]';
									}
								}
							}
						} elseif (!isset($opts->block_type)) {
							// render page blocks
							if ($snippets) {
								$html .= $this->blocks($snippets, $output[$opts->select]);
								if ($statics) {
									foreach ($statics as $stc) {
										$html .= $stc[0];
									}
								}
							} else {
								$html = '[unable to find snippets for page blocks]';
							}
						} else {
							$html = $this->repeat($innr, $repeat, $nest, $output, $opts->select, $opts->iterate, $convert);
						}
					} else {
						$html = $this->repeat($innr, $repeat, $nest, $output, $opts->select, $opts->iterate, $convert);
					}
				}
				break;
			}
			$html = preg_replace('#\soggeh-repeat#', '', $html);
			$html = preg_replace('#\soggeh-nest#', '', $html);
			$html = preg_replace('#\soggeh-field#', '', $html);
			$html = preg_replace('#\soggeh-static#', '', $html);
			$html = preg_replace('#\soggeh-snippet#', '', $html);
			$html = preg_replace('#\soggeh-album#', '', $html);
			$html = preg_replace('#\soggeh-search#', '', $html);
			$html = preg_replace('#\s(inject)="[^"]+"#', '', $html);
			$html = preg_replace('#(?:[\r\n]+)#s', '<br />', $html);
			return $html;
		}
		/*
		 * Get country code by language code
		 * @param string $code language code
		 * @return string
		 */
		function getCountryCodeByLang($code) {
	    foreach ($this->locale as $loc) {
        if (stristr($loc['lang'], strtolower($code).'-') && !is_numeric($loc['territory'])) {
          return strtolower($loc['territory']);
        }
	    }
	    return '';
		}
		/*
		 * Set url parameters in template
		 * @param string $html
		 * @return string
		 */
		protected function setURLParams($html) {
			$html = preg_replace('/{\$endpoint}/', $this->endpoint, $html);
			$html = preg_replace('/{\$api_key}/', self::$api_key, $html);
			$html = preg_replace('/{\$lang}/', $this->url_lang, $html);
			$html = preg_replace('/{\$dir}/', $this->direction, $html);
			$html = preg_replace('/{\$title}/', $this->app['title'], $html);
			$html = preg_replace('/{\$domain}/', $this->app['domain'], $html);
			$html = preg_replace('/{\$bucket}/', $this->app['bucket'], $html);
			$html = preg_replace('/{\$currency}/', $this->app['currency'], $html);
			$html = preg_replace('/{\$module}/', $this->url_module, $html);
			$html = preg_replace('/{\$param1}/', $this->url_child_id, $html);
			$html = preg_replace('/{\$param2}/', $this->url_extra_id, $html);
			preg_match_all('/{\$oggeh\-switch\|(?P<lang>.*?)}/', $html, $switch);
			if ($switch) {
				foreach ($switch[0] as $idx=>$sw) {
					$regex = str_replace('$', '\$', $sw);
					$regex = str_replace('-', '\-', $regex);
					$regex = str_replace('|', '\|', $regex);
					if (self::$rewrite) {
						if (stristr($this->uri, '/'.$this->url_lang.'/')) {
							$replace = str_replace('/'.$this->url_lang.'/', '/'.$switch['lang'][$idx].'/', $this->uri);
						} else {
							$replace = '/'.$switch['lang'][$idx].'/';
						}
					} else {
						if (stristr($this->uri, '?lang='.$this->url_lang)) {
							$replace = str_replace('?lang='.$this->url_lang, '?lang='.$switch['lang'][$idx], $this->uri);
						} else {
							$replace = '?lang='.$switch['lang'][$idx];
						}
					}
					$replace = str_replace('$', '{$}', $replace);
					$html = preg_replace('/'.$regex.'/', $replace, $html);
				}
			}
			preg_match_all('/{\$oggeh\-phrase\|(?P<key>.*?)}/', $html, $phrase);
			if ($phrase) {
				foreach ($phrase[0] as $idx=>$ph) {
					$regex = str_replace('$', '\$', $ph);
					$regex = str_replace('-', '\-', $regex);
					$regex = str_replace('|', '\|', $regex);
					$replace = $this->translate($phrase['key'][$idx]);
					$html = preg_replace('/'.$regex.'/', $replace, $html);
				}
			}
			return $html;
		}
		/*
		 * Set custom classes based on curent url.
		 * @param string $tpl parsed html template
		 * @return string
		 */
		protected function setMatchedClass($tpl) {
			$lang = $this->url_lang;
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = false;
			libxml_use_internal_errors(true);
			$dom->loadHTML(mb_convert_encoding($tpl, 'HTML-ENTITIES', 'UTF-8'));
			libxml_use_internal_errors(false);
			$active_set = false;
			foreach ($dom->getElementsByTagName('*') as $node) {
				if ($node->hasAttribute('oggeh-match')) {
					$match = $node->getAttribute('oggeh-match');
					if (stristr($match, '|')) {
						$match = explode('|', $match);
						$module = $match[0];
						$class = $match[1];
						$key = '';
						if (stristr($module, '/')) {
							$key = explode('/', $module);
							$module = $key[0];
							$key = $key[1];
						}
						if ($this->url_module == $module || $this->url_child_id == $key || is_numeric($module)) {
							if ($node->hasAttribute('class')) {
								$classes = $node->getAttribute('class');
								if (is_numeric($module)) {
									if (!$active_set) {
										$active_set = true;
										$node->setAttribute('class', $classes.' '.$class);
									}
								} else {
									$node->setAttribute('class', $classes.' '.$class);
								}
							} else {
								$node->setAttribute('class', $class);
							}
						}
					}
				}
			}
			if (!self::$rewrite) {
				foreach ($dom->getElementsByTagName('*') as $node) {
					if ($node->hasAttribute('href')) {
						$url = $node->getAttribute('href');
						if ($url != '' && $url != '#' && !stristr($url, '?') && !stristr($url, 'mailto:') && !stristr($url, 'http:') && !stristr($url, 'https:') && stristr($url, '/')) {
							$url = trim($url, '/');
							$segments = explode('/', $url);
							$query = '';
							if (count($segments)>0) {
								$query .= '?lang='.$segments[0];
							}
							if (count($segments)>1) {
								$query .= '&module='.$segments[1];
							}
							if (count($segments)>2) {
								$query .= '&param1='.$segments[2];
							}
							if (count($segments)>3) {
								$query .= '&param2='.$segments[3];
							}
							$node->setAttribute('href', $query);
						}
					}
				}
			}
			$tpl = $dom->saveHtml();
			return preg_replace('#\s(oggeh-match)="[^"]+"#', '', $tpl);
		}
		/*
		 * Get template output html.
		 * @param string $callback optional user defined callback function to print api response output
		 * @return string
		 */
		public function display($callback=null) {
			$lang = $this->url_lang;
			$locked_modules = self::$locked_modules;
			$unlock_users = self::$unlock_users;
			$lock = false;
			$html = '';
			$tpls = array();
			if ($this->url_module != 'inactive' && $this->published && is_file(self::$tpl_dir.'/header.html')) {
				$tpls[] = self::$tpl_dir.'/header.html';
			}
			if (in_array($this->url_module, $locked_modules)) {
				$lock = true;
				if (isset($_REQUEST['action'])) {
					$action = $_REQUEST['action'];
					# logout
					if ($action == 'logout' && isset($_SESSION['auth'])) {
						unset($_SESSION['auth']);
						session_destroy();
						header('WWW-Authenticate: Basic Realm="Restricted Area"');
						header('HTTP/1.0 401 Unauthorized');
						header('Location: /'.$lang.'/'.$this->url_module);
						exit;
					}
				}
				# authenticate
				$authorized = false;
				if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
					$user_name = $_SERVER['PHP_AUTH_USER'];
					$user_password = $_SERVER['PHP_AUTH_PW'];
					foreach ($unlock_users as $access) {
						if ($access['user'] == $user_name && $access['pass'] == $user_password) {
							$authorized = true;
							break;
						}
					}
					if ($user_name == $unlock_user && $user_password == $unlock_pass) {
						$authorized = true;
					}
				}
				# login
			  if (!$authorized || !isset($_SESSION['auth'])) {
					header('WWW-Authenticate: Basic Realm="Restricted Area"');
					header('HTTP/1.0 401 Unauthorized');
					$_SESSION['auth'] = true;
					$html .= '<div class="container">';
					$html .= '	<hr class="invisible" />';
					$html .= '	<div class="alert alert-danger" role="alert">';
					$html .= '		<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> '.$this->getPhrase('unauthorized_access').'</p>';
					$html .= '	</div>';
					$html .= '</div>';
				} else {
					$lock = false;
				}
			}
			if (!$lock) {
				if ($this->url_module != '404') {
					if ($this->published) {
						if ($this->url_child_id != '') {
							if ($this->url_extra_id != '' && is_file(self::$tpl_dir.'/'.$this->url_module.'.'.$this->url_child_id.'.html')) {
								$tpls[] = self::$tpl_dir.'/'.$this->url_module.'.'.$this->url_child_id.'.html';
							} elseif (is_file(self::$tpl_dir.'/'.$this->url_module.'.single.html')) {
								$tpls[] = self::$tpl_dir.'/'.$this->url_module.'.single.html';
							} elseif (is_file(self::$tpl_dir.'/'.$this->url_module.'.html')) {
								$tpls[] = self::$tpl_dir.'/'.$this->url_module.'.html';
							} else {
								if (is_file(self::$tpl_dir.'/404.html')) {
									$tpls[] = self::$tpl_dir.'/404.html';
								}
							}
						} else {
							if (is_file(self::$tpl_dir.'/'.$this->url_module.'.html')) {
								$tpls[] = self::$tpl_dir.'/'.$this->url_module.'.html';
							} else {
								if (is_file(self::$tpl_dir.'/404.html')) {
									$tpls[] = self::$tpl_dir.'/404.html';
								}
							}
						}
					} else {
						$tpls[] =self::$tpl_dir.'/'.$this->url_module.'.html';
					}
				} else {
					if (is_file(self::$tpl_dir.'/404.html')) {
						$tpls[] = self::$tpl_dir.'/404.html';
					}
				}
			}
			if ($this->url_module != 'inactive' && $this->published && is_file(self::$tpl_dir.'/footer.html')) {
				$tpls[] = self::$tpl_dir.'/footer.html';
			}
			if ($html == '') {
				$html =  $this->parse($tpls);
			}
			return $html;
		}
	}
?>
