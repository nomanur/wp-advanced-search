<?php
namespace WPAS;
require_once(dirname(__DIR__).'/src/RequestVar.php');
require_once(dirname(__DIR__).'/src/Input.php');
require_once(dirname(__DIR__).'/src/InputFormat.php');
require_once(dirname(__DIR__).'/src/InputBuilder.php');

class TestInputBuilder extends \WP_UnitTestCase {

    function setUp() {
        parent::setUp();
	    _clean_term_filters();
	    wp_cache_delete( 'last_changed', 'terms' );
    }

    public function testCanBuildSearch() {
        $args = array('field_type' => 'search',
                        'label' => 'Search',
                        'id' => 'search-id',
                        'format' => 'text',
                        'class' => array('testclass'),
                        'name' => 'my_search',
                        'attributes' => array('data-src' => 12345,
                                              'data-color' => 'red',
                                              'min' => 0,
                                              'max' => 100),
                        'default' => 'something');

        $input = InputBuilder::make('search_query', FieldType::search, $args);
        $this->assertTrue($input instanceof Input);

        $args = array('field_type' => 'search');
        $input = InputBuilder::make('search_query', FieldType::search, $args);
        $this->assertTrue($input instanceof Input);
        $this->assertTrue($input->getFormat() == InputFormat::text);
    }

    public function testCanBuildSubmit() {
        $args = array('field_type' => 'submit');
        $input = InputBuilder::make('submit', FieldType::submit, $args);
        $this->assertTrue($input instanceof Input);
        $this->assertTrue($input->getFormat() == InputFormat::submit);

        $args = array('field_type' => 'submit', 'values' => array("Go!"));
        $input = InputBuilder::make('submit', FieldType::submit,  $args);
        $this->assertTrue($input instanceof Input);
        $values = $input->getValues();
        $this->assertTrue(is_array($values) && $values[0] == "Go!");
    }

    public function testCanBuildTaxonomy() {
        $args = array(
                    'field_type' => 'taxonomy',
                    'taxonomy' => 'category',
                    'format' => 'select'
                );
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);
        $this->assertTrue($input instanceof Input);
        $this->assertFalse($input->isNested());

        $args = array(
            'field_type' => 'taxonomy',
            'taxonomy' => 'category',
            'format' => 'select',
            'nested' => true
        );
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);
        $this->assertTrue($input->isNested());
    }

    public function testTaxonomyAutoGenTerms() {

        $t = array();
        $t[] = $this->factory->category->create_and_get(array('name' =>  "Category One"));
        $t[] = $this->factory->category->create_and_get(array('name' =>  "Category Two"));
        $t[] = $this->factory->category->create_and_get(array('name' =>  "Z Category"));

        $args = array(
            'field_type' => 'taxonomy',
            'taxonomy' => 'category',
            'format' => 'select',
            'term_format' => 'slug'
        );
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);

        $values = $input->getValues();
        print_r($values);
        $this->assertTrue(count($values) == 4);

        // Test term_format = 'slug'
        $first_label = reset($values);
        $first_value = key($values);
        $this->assertTrue($first_label == $t[0]->name);
        $this->assertTrue($first_value == $t[0]->slug);

        // Test term_format = 'id'
        $args['term_format'] = 'id';
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);
        $values = $input->getValues();
        $first_value = key($values);
        $this->assertTrue(key($input->getValues()) == $t[0]->term_id);

        // Test term_format = 'name'
        $args['term_format'] = 'name';
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);
        $values = $input->getValues();
        $first_value = key($values);
        $this->assertTrue(key($input->getValues()) == $t[0]->name);

        // Test term_args
        $args['term_args'] = array('orderby' => 'name', 'order' => 'DESC');
        $input = InputBuilder::make('category', FieldType::taxonomy, $args);
        $values = $input->getValues();
        $this->assertTrue(key($input->getValues()) == $t[2]->name);

    }




}