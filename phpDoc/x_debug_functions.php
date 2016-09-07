<?php
/**
 * phpDoc для всех x_debug функций
 * @link http://xdebug.org/docs/all_functions
 * @version 1.0
 * @author hipot at wexpert dot ru
 */

/**
 * Returns the current stack depth level
 * @return int
 */
function xdebug_get_stack_depth () {}

/**
 * Returns information about the stack
 * @return array
 */
function xdebug_get_function_stack () {}

/**
 * Returns all the headers as set by calls to PHP's header() function
 * @return array
 */
function xdebug_get_headers () {}

/**
 * Displays the current function stack.
 * Displays the current function stack, in a similar way as what Xdebug would display in an error situation.
 */
function xdebug_print_function_stack () {}

/**
 * Returns declared variables
 * @return array
 */
function xdebug_get_declared_vars () {}

/**
 * Returns the calling class
 * @return string
 */
function xdebug_call_class () {}

/**
 * Returns the calling function/method
 * @return string
 */
function xdebug_call_function () {}

/**
 * Returns the calling file
 * @return string
 */
function xdebug_call_file () {}

/**
 * Returns the calling line
 * @return int
 */
function xdebug_call_line () {}

/**
 * Displays detailed information about a variable
 * This function displays structured information about one or more expressions
 * that includes its type and value. Arrays are explored recursively with values.
 */
function xdebug_var_dump () {}

/**
 * Displays information about a variable
 * This function displays structured information about one or more variables that
 * includes its type, value and refcount information. Arrays are explored
 * recursively with values. This function is implemented differently from PHP's
 * debug_zval_dump() function in order to work around the problems that that
 * function has because the variable itself is actually passed to the function.
 * Xdebug's version is better as it uses the variable name to lookup the variable
 * in the internal symbol table and accesses all the properties directly without
 * having to deal with actually passing a variable to a function. The result is
 * that the information that this function returns is much more accurate than PHP's own
 * function for showing zval information.
 */
function xdebug_debug_zval () {}

/**
 * Returns information about variables to stdout.
 */
function xdebug_debug_zval_stdout () {}

/**
 * Enables stack traces
 * @return void
 */
function xdebug_enable () {}

/**
 * Disables stack traces
 * @return void
 */
function xdebug_disable () {}

/**
 * Returns whether stack traces are enabled
 * @return bool
 */
function xdebug_is_enabled () {}

/**
 * Emits a breakpoint to the debug client
 * @return bool
 */
function xdebug_break () {}

/**
 * Starts a new function trace
 */
function xdebug_start_trace ($trace_file, $options) {}

/**
 * Stops the current function trace
 * @return void
 */
function xdebug_stop_trace () {}

/**
 * Returns the name of the function trace file
 * @return string
 */
function xdebug_get_tracefile_name () {}

/**
 * Returns the profile information filename
 * @return string
 */
function xdebug_get_profiler_filename () {}

function xdebug_dump_aggr_profiling_data () {}

function xdebug_clear_aggr_profiling_data () {}

/**
 * Returns the current memory usage
 * @return int
 */
function xdebug_memory_usage () {}

/**
 * Returns the peak memory usage
 * @return int
 */
function xdebug_peak_memory_usage () {}

/**
 * Returns the current time index
 * @return float
 */
function xdebug_time_index () {}

function xdebug_start_code_coverage () {}

function xdebug_stop_code_coverage () {}

/**
 * Starts recording all notices, warnings and errors and prevents their display
 * @return void
 */
function xdebug_start_error_collection () {}

/**
 * Stops recording of all notices, warnings and errors as started by xdebug_start_error_collection()
 * @return void
 */
function xdebug_stop_error_collection () {}

/**
 * Returns code coverage information
 * @return array
 */
function xdebug_get_code_coverage () {}

function xdebug_get_function_count () {}

/**
 * Displays information about super globals
 * @return void
 */
function xdebug_dump_superglobals () {}

define ('XDEBUG_TRACE_APPEND', 1);
define ('XDEBUG_TRACE_COMPUTERIZED', 2);
define ('XDEBUG_TRACE_HTML', 4);
define ('XDEBUG_CC_UNUSED', 1);
define ('XDEBUG_CC_DEAD_CODE', 2);
?>