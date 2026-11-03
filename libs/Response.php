<?php
namespace Symfony\Component\HttpFoundation;


/**
 * Abstract Model class
 */
abstract class Response 
{
	/**
	 * PHPStan-Fix: Symfony's Response::send() exists at runtime.
	 * @return static
	 */
	public function send() { return $this; }
}
