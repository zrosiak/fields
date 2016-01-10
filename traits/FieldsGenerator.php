<?php namespace Bm\Field\Traits;

use Yaml;

/**
 * Backend form fields generator
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
trait FieldsGenerator
{
    public $fields_filter = [
        'id'
    ];

    /**
     * Generowanie pól na podstawie konfiguracji szablonu
     * swap - {template_id: {tab: Nazwa taba}}
     *  nadpisuje konfiguracje komponentu dla danego szablonu
     * validator - dodaje walidację do modelu
     * messages - dodaje komunikaty walidacji do modelu
     * variable - jeśli wartość variable ustawiona i przypisana do innego pola
     *  to wartość pola zostanie napisana przez $post->{variable}
     * @return array
     */
    public function generateFields()
    {
        $form_fields = [];

        if (empty($this->template->field) === false) {
            foreach ($this->template->field as $field) {
                $field_config = Yaml::parse($field->code);
                $field_config['label'] = $field->label;
                $field_config['name'] = $field->name;

                // podmiana konfiguracji
                if (
                    isset($field_config['swap'])
                    && isset($field_config['swap'][$this->template->id])
                ) {
                    $field_config = array_merge(
                        $field_config,
                        $field_config['swap'][$this->template->id]
                    );
                }

                // walidacja
                if (isset($field_config['validator'])) {
                    $this->rules[$field->name] = $field_config['validator'];
                }

                // komunikaty walidacji
                if (isset($field_config['messages'])) {
                    $this->customMessages[] = $field_config['messages'];
                }

                // konwersja na json
                if (
                    isset($field_config['jsonable'])
                    && $field_config['jsonable'] == true
                ) {
                    $this->jsonable[] = $field->name;
                }

                $form_fields[$field->name] = $field_config;
            }
        }
        
        return $form_fields;
    }
}
