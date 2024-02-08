<?php

/**
 * Plugin Name: WooCommerce Event Tickets Plugin
 * Description: Permet aux utilisateurs de WooCommerce d'ajouté  des champs ACF personalisé pour un produit.
 * Version: 1.0
 * Author: Me
 */
//definir les champs ACF :
//ici on va vérifier si ACF est installé, si oui on va créer un groupe de champs ACF pour les détails de l'événement.
function check_acf_installed()
{
    //class_exists() est une fonction PHP qui vérifie si une classe est définie.la classe ACF est définie dans le plugin ACF.
    if (!class_exists('ACF')) {
        //admin_notices est un hook qui permet d'afficher des messages d'erreur ou de succès dans le back-office.
        add_action('admin_notices', function () {
            //on affiche un message d'erreur si ACF n'est pas installé.
            echo '<div class="notice notice-warning"><p>WooCommerce Event Tickets nécessite ACF pour fonctionner correctement.</p></div>';
        });
        return;
    }
    //si ACF est installé, on crée un groupe de champs ACF pour les détails de l'événement.
    if (function_exists('acf_add_local_field_group')):
        //acf_add_local_field_group() est une fonction ACF qui permet de créer un groupe de champs ACF.
        acf_add_local_field_group(array(
            'key' => 'group_event_details',
            'title' => 'Détails de l\'événement',
            'fields' => array(
                array(
                    'key' => 'field_event_date',
                    'label' => 'Date de l\'événement',
                    'name' => 'event_date',
                    'type' => 'date_time_picker',
                ),
                array(
                    'key' => 'filed_event_description',
                    'label' => 'Description de l\'événement',
                    'name' => 'event_description',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'field_event_privateInfo',
                    'label' => 'Informations privées',
                    'name' => 'event_private_info',
                    'type' => 'text',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ),
                ),
            ),
        ));
    endif;
}

//on choisit le hook init parce que c'est le premier hook qui est appelé après que WordPress ait fini de charger les plugins.
add_action('init', 'check_acf_installed');

// Creer un shortcode pour les informations de l'événement :


//ici ma fonction event_details shortcode() a pour argument $atts qui est un tableau associatif qui contient les attributs du shortcode.
function event_details_shortcode($atts)
{
    //ici on va définir les attributs du shortcode

    $atts = shortcode_atts(array(
        //on définit l'attribut id pour que l'utilisateur puisse spécifier
        // l'ID du produit pour lequel il veut afficher les détails de l'événement.
        'id' => get_the_ID()
        //le deuxieme argument de shortcode_atts() est un tableau associatif qui définit les attributs du shortcode
        // et le troisième argument est le nom du shortcode.
    ), $atts, 'event_details');

    //on initialise output à une chaîne vide parce que nous allons ajouter des données à cette chaîne.
    $output = '';
    //si l'ID est défini, on récupère les champs ACF et on les envois dans output.

    // si l'ID est défini dans l'attribut du shortcode, on récupère les champs ACF et on les envois dans output.
    if ($atts['id']) {
        //get_field() est une fonction ACF qui récupère la valeur d'un champ ACF
        $date = get_field('event_date', $atts['id']);
        $output .= '<div class="event-details">';
        //format de la date en Y-m-d h
        //ici strToTime() est une fonction PHP qui convertit une chaîne en timestamp.on utilisera le timestamp pour formater la date.
        $date = date('d-m-Y h:i', strtotime($date));
        // ici esc_attr() est une securité pour les attributs HTML.(évite les attaques XSS)
        $output .= '<div id="event-details" data-event-date="' . esc_attr($date) . '">';
        $output .= '<p>Date de l\'événement: ' . esc_html($date) . '</p>';
        $output .= '</div>';
        $output .= '<p>Description de l\'événement: ' . get_field('event_description', $atts['id']) . '</p>';
        $output .= '<p>Informations privées: ' . get_field('event_private_info', $atts['id']) . '</p>';
        $output .= '</div>';
    }

    return $output;
}

add_shortcode('event_details', 'event_details_shortcode');

//function enqueue js d'un compte a rebours si la date de l'événement est  a j - 1
function enqueue_countdown_script()
{
    // global $post est une variable globale qui contient des informations sur la page actuelle.
    global $post;
// is product est une fonction de woocommerce qui vérifie si la page actuelle est un produit.
    if (is_product() && get_field('event_date', $post->ID)) {
        var_dump(get_field('event_date', $post->ID));
        $eventDateTime = DateTime::createFromFormat('d/m/Y h:i a', get_field('event_date', $post->ID));
        if ($eventDateTime !== false) {
            $eventDate = $eventDateTime->format('Y-m-d');
            $tomorrow = new DateTime('+1 day');
            $tomorrowDate = $tomorrow->format('Y-m-d');

            if ($eventDate == $tomorrowDate) {
                echo '<div id="countdown"></div>';
                wp_enqueue_script('countdown', plugin_dir_url(__FILE__) . 'countdown.js', array('jquery'), '1.0', true);
            }
        }
    }
}

add_action('wp_enqueue_scripts', 'enqueue_countdown_script');

// function [private] content [/private] pour cacher le contenu de l'événement si l'utilisateur n'a pas acheté le produit en shortcode
function private_content_shortcode($atts, $content = null)
{
    $atts = shortcode_atts(array(
        'id' => get_the_ID()
    ), $atts, 'private_content');
    $output = '';
    if ($atts['id']) {
        // on verifie avec wc_customer_bought_product() si l'utilisateur a acheté le produit ,
        // les parametre sont le produit, l'utilisateur et le statut de la commande
        // commande que l'on peut retrouver dans woocommerce.
        if (wc_customer_bought_product('', get_current_user_id(), $atts['id'])) {
            $output = do_shortcode($content);
        } else {
            $output = '<p>Vous devez acheter un billet pour voir ce contenu.</p>';
        }
    }
    return $output;
}

//ajout du shortcode
add_shortcode('private_content', 'private_content_shortcode');
// add filter  pour que le shortcode soit exécuté dans le contenu
add_filter('the_content', 'do_shortcode');


