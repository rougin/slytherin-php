<?php namespace Slytherin;

use Pux\Executor;
use Pux\Mux;

/**
 * The Slytherin Application
 */

class Application {

	protected $_constructorArguments = array();

	protected $_controller = NULL;
	protected $_methods    = array();
	protected $_router     = NULL;

	/**
	 * Load the necessary configurations
	 */
	public function __construct(Mux $router)
	{
		$this->_checkDirectories();
		$this->_defineUrls();
		
		$this->_router = $router;
	}

	/**
	 * Run the application
	 */
	public function run()
	{
		$this->_getController();
		
		$route = $this->_router;

		/**
		 * Include the user's specified routes
		 */

		include 'app/config/routes.php';

		foreach ($this->_methods as $method => $parameters) {
			if ($method == '__construct') continue;

			if (empty($this->_constructorArguments)) {
				$options = array();
			} else {
				$options = array('constructor_args' => $this->_constructorArguments);
			}

			$regex    = array();
			$segments = NULL;

			/**
			 * Implode the parameters and create a regex pattern
			 */
			
			if (is_array($parameters)) {
				foreach ($parameters as $parameter => $defaultValue) {
					$segments .= '/:' . $parameter;

					$regex[$parameter] = '\w+';
					$regex[$parameter] = (gettype($defaultValue) == 'integer') ? '\d+' : $regex[$parameter];
				}

				/**
				 * Get the options
				 */

				$options['default'] = $parameters;
				$options['require'] = $regex;
			}

			/**
			 * Set the HTTP verb for the specified method
			 */

			switch ($method) {
				case 'destroy':
					$httpMethod = 'delete';
					break;
				case 'delete':
					$httpMethod = 'delete';
					break;
				case 'store':
					$httpMethod = 'post';
					break;
				case 'update':
					$httpMethod = 'put';
					break;
				default:
					$httpMethod = 'get';
					break;
			}

			/**
			 * Add an additional pattern for 'create' and 'edit' methods
			 */

			$pattern = '/' . $this->_controller . $segments;

			switch ($method) {
				case 'create':
					$pattern .= '/create';
					break;
				case 'edit':
					$pattern .= '/edit';
					break;
			}

			/**
			 * Define the specified route
			 */

			switch ($httpMethod) {
				case 'get':
					$route->get($pattern, array(ucfirst($this->_controller), $method), $options);
					break;
				case 'post':
					$route->post($pattern, array(ucfirst($this->_controller), $method), $options);
					break;
				case 'put':
					$route->put($pattern, array(ucfirst($this->_controller), $method), $options);
					break;
				case 'delete':
					$route->delete($pattern, array(ucfirst($this->_controller), $method), $options);
					break;
			}
		}

		/**
		 * Set the URL to be dispatch
		 */

		$url = str_replace(BASE_URL, '', CURRENT_URL);
		$url = (substr($url, -1) == '/') ? substr($url, 0, strlen($url) - 1) : $url;

		/**
		 * Dispatch and execute the route
		 */

		echo Executor::execute($route->dispatch('/' . strtok($url, '?')));
	}

	/**
	 * Check if the directories are already existed
	 * 
	 * @return error
	 */
	protected function _checkDirectories()
	{
		$directories = array(
			'app/',
			'app/controllers/',
			'app/config/',
			'app/libraries/',
			'app/models/',
			'app/views/'
		);

		foreach ($directories as $directory) {
			if ( ! file_exists($directory)) {
				return trigger_error('"' . $directory . '" cannot be found', E_USER_ERROR);
			}
		}
	}

	/**
	 * Define the base and current urls
	 */
	protected function _defineUrls()
	{
		/**
		 * Get the base url from the $_SERVER['HTTP_HOST']
		 */

		if (isset($_SERVER['HTTP_HOST'])) {
			$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST']
				. substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));
		} else {
			$baseUrl = 'http://localhost/';
		}

		/**
		 * Check the HTTP method from the form
		 */

		if (isset($_POST['_method']) && ($_POST['_method'] == 'PUT' || $_POST['_method'] == 'DELETE')) {
			$_SERVER['REQUEST_METHOD'] = $_POST['_method'];
			unset($_POST['_method']);
		}

		/**
		 * Define the following URLs
		 */

		define('BASE_URL', $baseUrl);
		define('CURRENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	/**
	 * Check the controller and get its contents
	 */
	protected function _getController()
	{
		/**
		 * Seperate the links from the string difference of BASE_URL and CURRENT_URL
		 */

		$segments = explode('/', str_replace(BASE_URL, '', CURRENT_URL));

		/**
		 * Set the first index as the controller
		 */
		
		if (empty($segments[0])) {
			return 0;
		}

		$this->_controller = strtok($segments[0], '?');

		$class       = new \ReflectionClass($controllerName);
		$constructor = $class->getConstructor();

		if ($constructor && count($constructor->getParameters()) != 0) {
			foreach ($constructor->getParameters() as $parameter) {
				/**
				 * Get the class name without needing the class to be loaded
				 */
				
				preg_match('/\[\s\<\w+?>\s([\w]+)/s', $parameter->__toString(), $matches);
				$object = isset($matches[1]) ? $matches[1] : NULL;

				if ($object) {
					$this->_constructorArguments[] = new $object();
				}
			}
		}

		foreach ($class->getMethods() as $method) {
			/**
			 * Add the curent method to the list of methods
			 */

			$this->_methods[$method->name] = NULL;

			/**
			 * Get the parameters for the each specified method
			 */

			foreach ($method->getParameters() as $parameter) {
				$this->_methods[$method->name][$parameter->name] = NULL;

				if ($parameter->isOptional()) {
					$this->_methods[$method->name][$parameter->name] = $parameter->getDefaultValue();
				}
			}
		}
	}

}