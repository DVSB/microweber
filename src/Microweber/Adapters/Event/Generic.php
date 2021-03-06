<?php
namespace Microweber\Adapters\Event;
class Generic
{
    public static $hooks = array();


    public static function on($event_name, $callback)
    {
        return self::event_bind($event_name, $callback);
    }

    public static function event_bind($function_name, $next_function_name = false)
    {
        if (is_bool($function_name)) {
             return self::$hooks;
        } else {
            if (!isset(self::$hooks[$function_name])) {
                self::$hooks[$function_name] = array();
            }
            self::$hooks[$function_name][] = $next_function_name;
        }
    }

    public static function emit($event_name, $data)
    {
        return self::event_trigger($event_name, $data);

    }

    public static function event_trigger($api_function, $data = false)
    {
        $hooks = self::$hooks;
        $return = array();
        if (isset(self::$hooks[$api_function]) and is_array(self::$hooks[$api_function]) and !empty(self::$hooks[$api_function])) {
            foreach (self::$hooks[$api_function] as $hook_key => $hook_value) {
                if ($hook_value != false) {
                    if (is_string($hook_value) and function_exists($hook_value)) {
                        if ($data != false) {
                            $return[$hook_value] = $hook_value($data);
                        } else {
                            $return[$hook_value] = $hook_value();
                        }
                        //unset(self::$hooks[$api_function][$hook_key]);
                    } else {
                        if (is_string($hook_value) or is_object($hook_value)) {
                            try {
                                if ($data != false) {
                                    if (is_string($hook_value)) {
                                        $return[$hook_value] = call_user_func($hook_value, $data);
                                    } else {
                                        call_user_func($hook_value, $data);
                                    }
                                } else {
                                    if (is_string($hook_value) and is_callable($hook_value)) {
                                        $return[$hook_value] = call_user_func($hook_value, function () {
                                            return true;
                                        });
                                    } elseif (is_callable($hook_value)) {

                                        $return[] = call_user_func($hook_value, function () {
                                            return true;
                                        });
                                    } elseif (is_string($hook_value)) {

                                        $try_class = explode('::', $hook_value);
                                        if (class_exists($try_class[0])) {
                                            $return[$hook_value] = call_user_func($hook_value, function () {
                                                return true;
                                            });
                                        }

                                    }

                                }
                            } catch (Exception $e) {

                            }
                        }
                    }
                }
            }
            if (!empty($return)) {
                return $return;
            }
        }
    }

    public static function action_hook($function_name, $next_function_name = false)
    {
        return self::event_bind($function_name, $next_function_name);
    }
}
