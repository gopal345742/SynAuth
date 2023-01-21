<?php
namespace webvimark\helpers;

/**
 * Class Singleton
 *
 * <example>
 * 	Singleton::setData('someKey', $someObject);
 *
 * 	$someObject = Singleton::getData('someKey');
 * </example>
 *
 * @package webvimark\helpers
 */
class Singleton
{
	private static $_instance;

	public $dataArray = array();

	private function __construct() {}

	private function __clone() {}

	/**
	 * getInstance
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if (null === self::$_instance)
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * setData
	 *
	 * @param string $to
	 * @param mixed $data
	 */
	public static function setData($to, $data)
	{
		$instance = self::getInstance();

		$instance->dataArray[$to] = $data;
	}

	/**
	 * getData
	 *
	 * @param string $from
	 * @param bool   $emptyReturn
	 *
	 * @return mixed
	 */
	public static function getData($from, $emptyReturn = false)
	{
		$instance = self::getInstance();

		return array_key_exists($from, $instance->dataArray) ? $instance->dataArray[$from] : $emptyReturn;
	}

	/**
	 * setData
	 *
	 * @param string|array $keys
	 */
	public static function clearData($keys)
	{
		$keys = (array) $keys;

		$instance = self::getInstance();

		foreach ($keys as $key)
		{
			unset($instance->dataArray[$key]);
		}
	}


	// ================= Improved version =================


	/**
	 * @var array
	 */
	protected static $data = [];

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public static function set($key, $value)
	{
		static::$data[$key] = $value;
	}

	/**
	 * @param string $key
	 * @param bool   $emptyResponse
	 *
	 * @return mixed
	 */
	public static function get($key, $emptyResponse = false)
	{
		return array_key_exists($key, static::$data) ? static::$data[$key] : $emptyResponse;
	}

	/**
	 * @param string $key
	 * @param \Closure   $value
	 *
	 * @return mixed
	 */
	public static function getAndStore($key, \Closure $value)
	{
		if ( array_key_exists($key, static::$data) )
		{
			return static::$data[$key];
		}
		else
		{
			$value = call_user_func($value);

			static::set($key, $value);

			return $value;
		}
	}

}