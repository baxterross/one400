<?php

require_once 'class-pexeto-customize-option.php';

class PexetoCustomizeOptionColor extends PexetoCustomizeOption{

	public function add_control($wp_customizer){
		parent::add_setting($wp_customizer, array(
			'sanitize_callback' => 'sanitize_hex_color'
			));
			 
		$wp_customizer->add_control(
			new WP_Customize_Color_Control(
				$wp_customizer,
				$this->id,
				array(
					'label' => $this->name,
					'section' => $this->section_id,
					'settings' => $this->id,
					'priority'=>$this->priority
				)
			)
		);
	}

	public function get_type(){
		return 'color';
	}
}