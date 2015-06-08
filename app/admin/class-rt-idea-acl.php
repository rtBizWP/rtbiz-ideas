<?php
/**
 * User: spock
 * Date: 19/11/14
 * Time: 12:15 PM
 */

if ( ! class_exists( 'Rt_Idea_ACL' ) ) {
    /**
     * Class Rt_HD_ACL
     * Add ACL(access control list) support to rtbiz-idea plugin
     *
     * @since 0.1
     */
    class Rt_Idea_ACL {
        /**
         * Hook for register rtbiz-idea module with rtbiz
         *
         * @since 0.1
         */
        public function __construct() {
            add_filter( 'rtbiz_modules', array( $this, 'register_rt_idea_module' ) );
        }

        /**
         * Register module rtbiz-idea
         *
         * @since 0.1
         *
         * @param $modules
         *
         * @return mixed
         */
        function register_rt_idea_module( $modules ) {
            $settings               = rt_idea_get_redux_settings();
            $module_key             = rtbiz_sanitize_module_key( RT_IDEA_TEXT_DOMAIN );
            $modules[ $module_key ] = array(
                'label'      => isset( $settings['rt_idea_menu_label'] ) ? $settings['rt_idea_menu_label'] : 'Idea',
                'post_types' => array( RTBIZ_IDEAS_SLUG ),
                'require_user_groups' => false,
                'offering_support' => array( RTBIZ_IDEAS_SLUG ),
            );

            return $modules;
        }
    }
}
