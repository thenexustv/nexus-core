<?php

    if(!class_exists('WP_List_Table')){
       require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

    }

    class Series_Table extends WP_List_Table {

        public function __construct() {
            parent::__construct(array(
                'singular' => 'wp_list_series',
                'plural' => 'wp_list_series',
                'ajax' => false
            ));
        }

        public function get_columns() {
            return array(
                'series_name' => 'Name',
                'series_slug' => 'Slug',
                'series_episode_count' => 'Episode Count',
                'series_id' => 'ID',
            );
        }


        public function get_sortable_columns() {
            return array();
        }

        public function get_hidden_columns() {
            return array();
        }

        public function prepare_items() {

            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();

            $data = $this->table_data();

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $data;

        }

        public function table_data() {

            $categories = get_categories();
            $data = array();

            foreach ($categories as $index => $category) {
                if ( $category->name == 'uncategorized' ) {
                    unset($categories[$index]);
                }
            }

            return $categories;

        }

        public function column_series_id($item) {
            return $item->term_id;
        }

        public function column_series_name($item) {

            $page = sprintf('nexus-core-series-%1$s-settings', $item->term_id);

            $actions = array(
                'edit' => sprintf('<a href="?page=%s&action=%s">Edit</a>', $page, 'edit')
            );

            return sprintf('%1$s %2$s', $item->name, $this->row_actions($actions));
        }
        
        public function column_series_episode_count($item) {
            return $item->count;
        }

        public function column_series_slug($item) {
            return $item->slug;
        }

        public function column_default($item, $column_name) {
            switch ($column_name) {

                default:
                    return $item[$column_name];

            }
        }


    }

?>
<div class="wrap">
    
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <p>View and edit the series specific settings in this Podcast Network.</p>

    <?php
        $wp_table = new Series_Table();
        $wp_table->prepare_items();
        $wp_table->display();
    ?>


</div>