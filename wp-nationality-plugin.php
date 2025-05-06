<?php
/**
 * Plugin Name: wpDataTables Nationality Support
 * Description: Stores each user’s nationality on the profile screen and injects it into wpDataTables through %VAR1%.
 * Author: Vic Levy interacting with ChatGPT o3
 * Version: 1.1.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct access
}

/* -------------------------------------------------------------------------
 * 1. USER-PROFILE FIELD
 * ---------------------------------------------------------------------- */

/**
 * Outputs the “Nationality” field on user-profile screens.
 *
 * @param WP_User $user Currently edited user.
 */
function wdt_nationality_show_field( WP_User $user ): void {
    // Whitelist of choices; expand as needed.
    $choices = [
        ''   => '— Select —',
        'US' => 'U.S.',
        'UK' => 'U.K.',
    ];

    $value = get_user_meta( $user->ID, 'nationality', true );
    ?>
        <h2>Additional Profile Information</h2>
        <table class="form-table">
            <tr>
                <th><label for="nationality">Nationality</label></th>
                <td>
                    <select name="nationality" id="nationality">
                        <?php
                        foreach ( $choices as $code => $label ) {
                            printf(
                                '<option value="%1$s" %2$s>%3$s</option>',
                                esc_attr( $code ),
                                selected( $value, $code, false ),
                                esc_html( $label )
                            );
                        }
                        ?>
                    </select>
                    <p class="description">Select the user’s nationality.</p>
                </td>
            </tr>
        </table>
    <?php
}
add_action( 'show_user_profile', 'wdt_nationality_show_field' );
add_action( 'edit_user_profile', 'wdt_nationality_show_field' );

/**
 * Saves the “Nationality” field when a user profile is updated.
 *
 * @param int $user_id ID of the user being saved.
 */
function wdt_nationality_save_field( int $user_id ): void {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( isset( $_POST['nationality'] ) ) {
        $new_value = sanitize_text_field( wp_unslash( $_POST['nationality'] ) );
        update_user_meta( $user_id, 'nationality', $new_value );
    }
}
add_action( 'personal_options_update', 'wdt_nationality_save_field' );
add_action( 'edit_user_profile_update', 'wdt_nationality_save_field' );

/* -------------------------------------------------------------------------
 * 2. SHORTCODE WRAPPER
 * ---------------------------------------------------------------------- */

/**
 * [wpdatatable_national] – wraps wpDataTables and sets var1=<nationality>.
 *
 * Example:
 *     [wpdatatable_national id="17" fallback="US"]
 *
 * Attributes:
 *   id       (required) – wpDataTables table ID.
 *   fallback (optional) – Default nationality when user meta is missing.
 *
 * @param array<string,string|int> $atts Shortcode attributes.
 * @return string Rendered table.
 */
function wdt_nationality_shortcode( array $atts ): string {
    $atts = shortcode_atts(
        [
            'id'       => 0,
            'fallback' => '',
        ],
        $atts,
        'wpdatatable_national'
    );

    $table_id = absint( $atts['id'] );
    if ( 0 === $table_id ) {
        return '<p><strong>Error:</strong> Table ID missing.</p>';
    }

    // Fetch nationality, falling back if needed.
    $user_id      = get_current_user_id();
    $nationality  = '';
    if ( $user_id > 0 ) {
        $nationality = (string) get_user_meta( $user_id, 'nationality', true );
    }
    if ( '' === $nationality ) {
        $nationality = sanitize_text_field( $atts['fallback'] );
    }

    // Build inner wpDataTables shortcode.
    $inner = sprintf(
        '[wpdatatable id="%d" var1="%s"]',
        $table_id,
        esc_attr( $nationality )
    );

    return do_shortcode( $inner );
}
add_shortcode( 'wpdatatable_national', 'wdt_nationality_shortcode' );
