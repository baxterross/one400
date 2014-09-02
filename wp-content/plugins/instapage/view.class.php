<?php
/*
 * Copyright (C) 2011 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @package LiteEntityLib
 */

class InstapageView
{
	protected $template_data = array();
	protected $templates = null;

	public function __construct( $templates = null, $attributes = null )
	{
		if( $attributes ) foreach( $attributes as $key => $value )
		{
			$this->template_data[ $key ] = $value;
		}

		$this->templates = $templates;
	}

	public function __set( $name, $value )
	{
		$this->template_data[ $name ] = $value;
	}

	public function __toString()
	{
		return 'use $view->fetch() instead';
	}

	public function assign( $key, $value )
	{
		$this->template_data[ $name ] = $value;

		return $this;
	}

	public function fetch( $templates = null )
	{
		$templates = $templates ? $templates : $this->templates;

		if( !$templates || empty( $templates ) )
		{
			throw new Exception( "Templates can not be null." );
		}

		if( !is_array( $templates ) )
		{
			$templates = array( $templates );
		}

		foreach( $templates as $template )
		{
			if( !file_exists( $template ) )
			{
				throw new Exception( "Template {$template} not found." );
			}

			if( $this->template_data ) foreach( $this->template_data as $variable_name => $variable_value )
			{
				$$variable_name = $variable_value;
				unset( $variable_name );
				unset( $variable_value );
			}

			ob_start();
			include( $template );
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}

	public static function get( $template, $variables = null )
	{
		$view = new View( $template );

		if( $variables ) foreach( $variables as $key => $value )
		{
			$view->$key = $value;
		}

		return $view->fetch();
	}

	public static function _( $template, $variables = null )
	{
		return self::get( $template, $variables );
	}
}
