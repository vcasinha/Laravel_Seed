<?php
    namespace Seed;
    /**
     * data class
     *
     * Data class versatile object<br/>
     * Can be used as an array
     *
     * @implements Iterator
     * @category Seed
     * @package Seed
     */

    /**
     * Check if var is of type Oo_Data
     * @param  mixed  $var [description]
     * @return boolean
     */
    function is_data( $var )
    {
        return ( $var instanceof oo_data );
    }

    class Data extends \ArrayIterator
    {
        /**
         * Storage for magic properties
         *
         * (default value: array())
         *
         * @var array
         * @access protected
         */
        protected $array_data = array();

        /**
         * Constructor allows loading of data "automagically"
         *
         * @access public
         * @param array $args. (default: array())
         * @param bool $recursive. (default: false)
         * @return void
         */
        function __construct( $args = array(), $recursive = false )
        {
            if( count( $args ) > 0 )
            {
                $this->merge( $args, $recursive );
            }
        }

        /**
         * Get property value
         *
         * @access public
         * @param mixed $variable
         * @param mixed $default. (default: null)
         * @return void
         */
        public function get( $name, $default = NULL )
        {
            if( key_exists( $name, $this->array_data ) === false )
            {
                return $default;
            }

            return $this->array_data[ $name ];
        }

        /**
         * Set property value
         *
         * @access public
         * @param mixed $name
         * @param mixed $value
         * @return void
         */
        public function set( $name, $value = NULL )
        {
            if( is_data( $name ) )
            {
                $name = $name->toArray( );
            }

            if( is_array( $name ) )
            {
                $this->array_data = array();
                $this->merge( $name, true );
            }
            else
            {
                $this->array_data[ $name ] = $value;
            }

            return $this;
        }

        /**
         * Magic method to set property values
         *
         * @access public
         * @param mixed $name
         * @param mixed $value
         * @return void
         */
        function __set( $name, $value )
        {
            return $this->set( $name, $value );
        }

        /**
         * Magic method to get property values
         *
         * @access public
         * @param mixed $name
         * @return void
         */
        function __get( $name )
        {
            return $this->get( $name );
        }

        /**
         * Magic method allows isset functionalty on class properties
         *
         * @access public
         * @param mixed $name
         * @return void
         */
        function __isset( $name )
        {
            if( $this->exists( $name ) == false )
            {
                return false;
            }

            return true;
        }

        function keys()
        {
            return array_keys( $this->array_data );
        }

        function values()
        {
            return array_values( $this->array_data );
        }

        /**
         * Magic method allows unset functionality on class properties
         *
         * @access public
         * @param mixed $name
         * @return void
         */
        public function __unset( $key )
        {
            unset( $this->array_data[ $key ] );
            return $this;
        }

        public function __sleep()
        {
            return array_keys( get_object_vars( $this ) );
        }

        public function filterKeys( $keys )
        {
            if( is_data( $keys ) === true )
            {
                $keys = $keys->toArray();
            }

            if( is_string( $keys ) === true and func_num_args() > 1 )
            {
                $keys = func_get_args();
            }

            if( is_array( $keys ) === false )
            {
                throw new Exception( "Invalid keys to filter" );
            }

            foreach( $this->array_data as $key => $value )
            {
                if( in_array( $key, $keys, true ) === false )
                {
                    $this->__unset( $key );
                }
            }

            return $this;
        }

        /**
         * Reset data to empty values
         *
         * @access public
         * @return void
         */
        public function reset()
        {
            $this->array_data = array();
            return $this;
        }

        /**
         * Return the amount of properties defined
         *
         * @access public
         * @return void
         */
        function count()
        {
            return count( $this->array_data );
        }

        /**
         * Return an array with all class properties
         *
         * @access public
         * @param bool $recursive. (default: false)
         * @return void
         */
        public function toArray( $recursive = false )
        {
            $output = array();

            if( $recursive === false )
            {
                return $this->array_data;
            }

            foreach( $this->array_data as $index => $item )
            {
                if( $item instanceof PDOStatement )
                {
                    $rows = array();
                    foreach( $item as $row )
                    {
                        $rows[] = $row->toArray( true );
                    }

                    $output[ $index ] = $rows;
                }
                elseif( $item instanceof oo_data )
                {
                    $output[ $index ] = $item->toArray( true );
                }
                elseif( is_array( $item ) )
                {
                    $output[ $index ] = $this->arrayDump( $item, true );
                }
                else
                {
                    $output[ $index ] = $item;
                }
            }

            return $output;
        }

        /**
         * Helper method used by to_array method
         *
         * @access protected
         * @param mixed $var
         * @param bool $recursive. (default: false)
         * @return void
         */
        protected function arrayDump( $var, $recursive = false )
        {
            if( $var instanceof oo_data )
            {
                $var = $var->to_array( $recursive );
            }

            if( is_array( $var ) === false )
            {
                return false;
            }

            foreach( $var as $key => $value )
            {
                if( is_array( $value ) )
                {
                    $var[ $key ] = $this->arrayDump( $value, $recursive );
                }
                elseif( $recursive === true and $value instanceof oo_data )
                {
                    $var[ $key ] = $value->toArray( true );
                }
                elseif( is_object( $value ) )
                {
                    $var[ $key ] = '(OBJECT)' . get_class( $value );
                }
            }

            return $var;
        }

        /**
         * Return object structure in json format
         *
         * @access public
         * @return void
         */
        public function toJSON()
        {
            return json_encode( $this->toArray( true ) );
        }

        public function __toString()
        {
            return $this->toJSON();
        }

        /**
         * print_r functionality
         *
         * @access public
         * @param bool $return. (default: false)
         * @param bool $recursive. (default: false)
         * @return void
         */
        function toDump( $recursive = false )
        {
            return print_r( $this->toArray( $recursive ), true );
        }

        /**
         * Remove empty string elements
         *
         * @access public
         * @return void
         */
        public function removeEmpty()
        {
            foreach( $this->array_data as $key => $value )
            {
                if( is_array( $value ) or $value instanceof data )
                {
                    continue;
                }

                if( strlen( $value ) == 0 )
                {
                    unset( $this->$key );
                }
            }

            return $this;
        }

        /**
         * Merge array or data object with this object
         *
         * @access public
         * @param mixed $params
         * @param bool $recursive. (default: false)
         * @return void
         */
        public function merge( $params, $recursive = false )
        {
            if( $params instanceof stdClass )
            {
                $params = get_object_vars( $params );
            }

            if( is_data( $params ) === false and is_array( $params ) === false )
            {
                throw new Exception( 'Params not array nor data ' . print_r( $params, true ) );
            }

            foreach( $params as $key => $value )
            {
                if( $recursive == true )
                {
                    if( is_array( $value ) or is_data( $value ) )
                    {
                        $value = new oo_data( $value, true );
                    }
                }


                $this->set( $key, $value );
            }
        }

        /**
         * Shift value from object
         *
         * @access public
         * @return void
         */
        public function shift()
        {
            return array_shift( $this->array_data );
        }

        /**
         * unshift value to object
         *
         * @access public
         * @param mixed $value
         * @return void
         */
        public function unshift( $value )
        {
            array_unshift( $this->array_data, $value );
            return $this;
        }

        /**
         * pop value from object.
         *
         * @access public
         * @param mixed $default. (default: null)
         * @return void
         */
        public function pop( $default = null )
        {
            $value = array_pop( $this->array_data );
            if( $value == '' )
            {
                $value = $default;
            }

            return $value;
        }

        /**
         * push value to object.
         *
         * @access public
         * @param mixed $value
         * @return void
         */
        public function push( $value )
        {
            array_push( $this->array_data, $value );
            return $this;
        }

        /**
         * Check if key exists
         *
         * @access public
         * @param mixed $key
         * @return void
         */
        public function exists( $key )
        {
            if( key_exists( $key, $this->array_data) === false )
            {
                return false;
            }

            return true;
        }

        public function implode( $glue )
        {
            return implode($glue, $this->array_data);
        }

        public function slice( $offset, $length = null)
        {
            return array_slice( $this->array_data, $offset, $length);
        }

        public function ksort()
        {
            ksort( $this->array_data );
        }

        public function krsort()
        {
            krsort( $this->array_data);
        }

        public function sort()
        {
            sort( $this->array_data );
        }

        public function rsort()
        {
            rsort( $this->array_data );
        }

        /**
         * Check if value is in the object
         *
         * @access public
         * @param mixed $value
         * @return void
         */
        public function valueExists( $value )
        {
            return in_array( $value, $this->array_data );
        }

        /**
         * rewind object function.
         *
         * @access public
         * @return wip_data
         */
        public function rewind()
        {
            reset( $this->array_data );
            return $this;
        }

        /**
         * Return current value
         *
         * @access public
         * @return void
         */
        public function current()
        {
            return current( $this->array_data );
        }

        /**
         * Return current key
         *
         * @access public
         * @return void
         */
        public function key()
        {
            return key( $this->array_data );
        }

        /**
         * Move to next position
         *
         * @access public
         * @return void
         */
        public function next()
        {
            return next( $this->array_data );
        }

        /**
         * Check if current key is valid
         *
         * @access public
         * @return void
         */
        public function valid()
        {
            if( $this->key() !== null )
            {
                return true;
            }

            return false;
        }

        public function offsetSet($offset, $value)
        {
            $this->array_data[$offset] = $value;
        }

        public function offsetExists($offset)
        {
            return isset($this->array_data[$offset]);
        }

        public function offsetUnset($offset)
        {
            unset($this->array_data[$offset]);
        }

        public function offsetGet( $offset )
        {
            return isset($this->array_data[ $offset ]) ? $this->array_data[$offset] : null;
        }

        public function __clone()
        {
            $this->_clone = true;
        }

        public function fromYAMLFile( $location )
        {
           if( file_exists( $location ) === false )
           {
               throw new Exception( "YAML not found on " . $location );
           }

           $yaml = file_get_contents( $location );
           if( $yaml === false )
           {
               throw new Exception( "Problem loading YAML file " . $location );
           }

           try
           {
               $this->fromYAML( $yaml );
           }
           catch( Exception $e )
           {
               throw new Exception( "Invalid YAML File " . $location );
           }

           return $this;
        }

        static function loadYAML( $file_location )
        {
            $data = new Oo_Data();
            $data->fromYAMLFile( $file_location );
            return $data;
        }

        public function fromYAML( $yaml )
        {
            $data = yaml_parse( $yaml );
            if( $data === false )
            {
                throw new Exception( "Invalid YAML" );
            }

            $this->merge( $data, true );
            return $this;
        }

        public function toYAML()
        {
           return yaml_emit( $this->toArray( true ), YAML_UTF8_ENCODING, YAML_LN_BREAK);
        }
    }
