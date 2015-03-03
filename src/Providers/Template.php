<?php namespace Baun\Providers;

use Baun\Interfaces\Template as TemplateInterface;

class Template implements TemplateInterface {

	protected $template;
	protected $customData;

	public function __construct($templates_path)
	{
		$loader = new \Twig_Loader_Filesystem($templates_path);
		$this->template = new \Twig_Environment($loader);
	}

	public function render($template, $data = [])
	{
		echo $this->template->render($template, $data);
	}

	public function custom($name, $data)
	{
		$this->customData[$name] = $data;

		if (method_exists($this, 'custom_' . $name)) {
			$function = new \Twig_SimpleFunction($name, [$this, 'custom_' . $name], ['is_safe' => ['html']]);
			$this->template->addFunction($function);
		}
	}

	public function custom_baun_nav()
	{
		$data = $this->customData['baun_nav'];
		$html = $this->navToHTML($data);
		echo $html;
	}

	private function navToHTML($array, $top = true)
	{
		if ($top) {
			$html = '<ul class="baun-nav">';
		} else {
			$html = '<ul>';
		}

		foreach ($array as $key => $value) {
			if (!is_int($key)) {
				if (preg_match('/^\d+\-/', $key)) {
					list($index, $path) = explode('-', $key, 2);
					$key = $path;
				}
				$title = ucwords(str_replace(['-', '_'], ' ', basename($key)));
				$html .= '<li class="baun-nav-item baun-nav-has-children">' . $title . $this->navToHTML($value, false) . '</li>';
			} else {
				$html .= '<li class="baun-nav-item item-' . $key . ($value['active'] ? ' baun-nav-active' : '') . '">';
				$html .= '<a href="' . ($value['url'] == '/' ? $value['url'] : '/' . $value['url']) . '">' . $value['title'] . '</a>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';
		return $html;
	}

}