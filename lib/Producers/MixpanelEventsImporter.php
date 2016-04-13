<?php
require_once(dirname(__FILE__) . "/MixpanelBaseProducer.php");
require_once(dirname(__FILE__) . "/MixpanelPeople.php");
require_once(dirname(__FILE__) . "/../ConsumerStrategies/CurlConsumer.php");

/**
 * Provides an API to importing events older than 5 days on Mixpanel
 */
class Producers_MixpanelEventsImporter extends Producers_MixpanelBaseProducer {

    /**
     * An array of properties to attach to every tracked event
     * @var array
     */
    private $_super_properties = array("mp_lib" => "php");


    /**
     * Track an event defined by $event associated with metadata defined by $properties
     * @param string $event
     * @param array $properties
     */
    public function import($event, $properties = array()) {

        // if no token is passed in, use current token
        if (!array_key_exists("token", $properties)) $properties['token'] = $this->_token;

        // if no time is passed in, use current time minus 5 days
        if (!array_key_exists('time', $properties)) $properties['time'] = strtotime('-5 days');

        $params['event'] = $event;
        $params['properties'] = array_merge($this->_super_properties, $properties);

        $this->enqueue($params);
    }


    /**
     * Register a property to be sent with every event. If the property has already been registered, it will be
     * overwritten.
     * @param string $property
     * @param mixed $value
     */
    public function register($property, $value) {
        $this->_super_properties[$property] = $value;
    }


    /**
     * Register multiple properties to be sent with every event. If any of the properties have already been registered,
     * they will be overwritten.
     * @param array $props_and_vals
     */
    public function registerAll($props_and_vals = array()) {
        foreach($props_and_vals as $property => $value) {
            $this->register($property, $value);
        }
    }


    /**
     * Register a property to be sent with every event. If the property has already been registered, it will NOT be
     * overwritten.
     * @param $property
     * @param $value
     */
    public function registerOnce($property, $value) {
        if (!isset($this->_super_properties[$property])) {
            $this->register($property, $value);
        }
    }


    /**
     * Register multiple properties to be sent with every event. If any of the properties have already been registered,
     * they will NOT be overwritten.
     * @param array $props_and_vals
     */
    public function registerAllOnce($props_and_vals = array()) {
        foreach($props_and_vals as $property => $value) {
            if (!isset($this->_super_properties[$property])) {
                $this->register($property, $value);
            }
        }
    }


    /**
     * Un-register an property to be sent with every event.
     * @param string $property
     */
    public function unregister($property) {
        unset($this->_super_properties[$property]);
    }


    /**
     * Un-register a list of properties to be sent with every event.
     * @param array $properties
     */
    public function unregisterAll($properties) {
        foreach($properties as $property) {
            $this->unregister($property);
        }
    }


    /**
     * Get a property that is set to be sent with every event
     * @param string $property
     * @return mixed
     */
    public function getProperty($property) {
        return $this->_super_properties[$property];
    }


    /**
     * Identify the user you want to associate to imported events
     * @param string|int $user_id
     */
    public function identify($user_id) {
        $this->register("distinct_id", $user_id);
    }


    /**
     * Returns the "events" endpoint
     * @return string
     */
    function _getEndpoint() {
        return $this->_options['events_import_endpoint'] . '/?api_key=' . $this->_apiKey;
    }
}
