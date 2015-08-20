<?php

namespace Nexus\Metaboxes;

class MetaboxView {

    private $id = 'generic-view';

    private $name = '';

    private $fields = array();

    public function __construct($id, $name) {
      $this->name = $name;
      $this->id = $id;
    }

    public function add_field($id, $title, $callback, $args = array()) {

      $this->fields[$id] = array(
        'id' => $id,
        'title' => $title,
        'callback' => $callback,
        'args' => $args
      );

    }

    public function render() {

      echo $this->render_table();

    }

    public function render_table() {

      $html = '
        <div id="%1$s" class="section">

          <h3>%2$s</h3>

          <table class="form-table">
          %3$s
          </table>

        </div>
      ';

      $fields = $this->render_fields();

      return sprintf($html, esc_attr($this->id), $this->name, $fields);

    }

    public function render_fields() {

      $output = '';
      $html = '
        <tr class="field %1$s">
          <th scope="row">
            <label for="%1$s">%2$s</label>
          </th>
          <td>
            %3$s
          </td>
        </tr>
      ';

      foreach ( $this->fields as $field ) {
        $output .= sprintf( $html, esc_attr($field['id']), $field['title'], call_user_func($field['callback'], $field['args'], $field, $this));
      }

      return $output;

    }

	public function get_field_name($name) {
		return sprintf('%s[%s]', $this->id, $name);
	}

	public function get_field_id($id) {
		return sprintf( '%s__%s', $this->id, $id );
	}
}
