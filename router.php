<?php
class Router {
	private $method;
	private $url;
	private $query_arr;
	private $context;
	public $values;
	public $arguments;

	public function __construct() {
		$context = str_replace('/index.php', '', $_SERVER['PHP_SELF']);
		$context_arr = explode($context, $_SERVER['REQUEST_URI']);
		
		$this->context = $context;
		$this->method  = $_SERVER['REQUEST_METHOD'];
		$this->url     = array_pop($context_arr);
		$this->values  = array();
		$this->arguments  = array();

		// Defaults - install
		$this->all('/install', function($req){
			include ELEMENTARY_PATH.'install.php';
		});
		
		// Defaults - template
		$this->get('/template/{path/}', function($req, $path){
			$req->print(ELEMENTARY_PATH.'templates/'.$path.'.html');
		});
	}

	// Take url string; Return url query array
	private function query($url) {
		$regex = preg_replace("|{([^}]+)/}|", '(.+)', $url);
		$regex = preg_replace("|{([^}]+)}|", '([^/]+)', $regex);
		$query = array();
		
		preg_match_all("|{([^}]+)}|", $url, $keys);
		preg_match_all("|".$regex."|", $this->url, $vals);
		preg_match("|".$regex."|", $this->url, $url_match);

		if (count($vals[0]) && count($keys[0]) && count($url_match)) {
			foreach ($keys[1] as $i => $k) {
				$query[$k] = $vals[$i + 1][0];
			}
		}
		return $query;
	}

	// Take method, url, [template name | callback], callback; this
	private function enroute($method, $url, $name, $fun) {
		if (is_callable($name)) {
			$fun = $name;
			$name = null;
		}
		
		if ($this->method === $method || $method === 'ALL') {
			$query = $this->query($url);
			
			if ($this->url === $url.'/' || $this->url === $url || count($query)) {
				$request = new Request($this, $query, $name);
				$args = count($query) ? array_values($query) : array();
				array_unshift($args, $request);
				if ($fun) call_user_func_array($fun, $args);
				else {
					$request->controller()->process($_POST);
					$request->view()->render();
				}
				exit();
			}
		}
	}
	
	public function data($data) {
		$this->values = $data;
		return $this;
	}

	public function get($url = null, $name = null, $fun = null) {
		$this->enroute('GET', $url, $name, $fun);
		return $this;
	}

	public function post($url = null, $name = null, $fun = null) {
		$this->enroute('POST', $url, $name, $fun);
		return $this;
	}

	public function all($url = null, $name = null, $fun = null) {
		$this->enroute('ALL', $url, $name, $fun);
		return $this;
	}

	public function redirect($url = null) {
		if ($url) header('Location: '.$url);
		return $this;
	}
}
