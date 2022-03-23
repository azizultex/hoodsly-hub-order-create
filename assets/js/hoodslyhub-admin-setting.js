'use strict';

jQuery(document).ready(function ($) {
    $('.wp-color-picker-field').wpColorPicker();

    /*
        $('.selecttwo-select').select2({
            placeholder: hoodslyhubadminsettingObj.please_select,
            allowClear: false
        });*/

    var activetab = '';
    if (typeof (localStorage) !== 'undefined') {
        activetab = localStorage.getItem('hoodslyhubactivetab');
    }


    //if url has section id as hash then set it as active or override the current local storage value
    if (window.location.hash) {
        if ($(window.location.hash).hasClass('global_setting_group')) {
            activetab = window.location.hash;
            if (typeof (localStorage) !== 'undefined') {
                localStorage.setItem('hoodslyhubactivetab', activetab);
            }
        }
    }


    if (activetab !== '' && $(activetab).length && $(activetab).hasClass('global_setting_group')) {
        $('.global_setting_group').hide();
        $(activetab).fadeIn();
    }

    if (activetab !== '' && $(activetab + '-tab').length) {
        $('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
        $(activetab + '-tab').addClass('nav-tab-active');

        $('.nav-tab-wrapper li.nav-parent-active').removeClass('nav-parent-active');
        $(activetab + '-tab').closest('li').addClass('nav-parent-active');
    }

    $('.nav-tab-wrapper a').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);

        $('.nav-tab-wrapper a.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active').blur();

        $('.nav-tab-wrapper li.nav-parent-active').removeClass('nav-parent-active');
        $this.closest('li').addClass('nav-parent-active');

        var clicked_group = $(this).attr('href');

        if (typeof (localStorage) !== 'undefined') {
            localStorage.setItem('hoodslyhubactivetab', $(this).attr('href'));
        }
        $('.global_setting_group').hide();
        $(clicked_group).fadeIn();
    });


    $('.wpsa-browse').on('click', function (event) {
        event.preventDefault();

        var self = $(this);

        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: self.data('uploader_title'),
            button: {
                text: self.data('uploader_button_text')
            },
            multiple: false
        });

        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            self.prev('.wpsa-url').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });


    //make the subheading single row
    $('.setting_heading').each(function (index, element) {
        var $element        = $(element);
        var $element_parent = $element.parent('td');
        $element_parent.attr('colspan', 2);
        $element_parent.prev('th').remove();
        $element_parent.parent('tr').removeAttr('class');
        $element_parent.parent('tr').addClass('global_setting_heading_section');
    });


    $('.setting_subheading').each(function (index, element) {
        var $element        = $(element);
        var $element_parent = $element.parent('td');
        $element_parent.attr('colspan', 2);
        $element_parent.prev('th').remove();
        $element_parent.parent('tr').removeAttr('class');
        $element_parent.parent('tr').addClass('global_setting_subheading_section');
    });


    $('.global_setting_group').each(function (index, element) {
        var $element    = $(element);
        var $form_table = $element.find('.form-table');
        $form_table.prev('h2').remove();

        var $i = 0;
        $form_table.find('tr').each(function (index2, element) {
            var $tr = $(element);

            if (!$tr.hasClass('global_setting_heading_section')) {
                $tr.addClass('global_setting_common_section');
                $tr.addClass('global_setting_common_section_' + $i);
            } else {
                $i++;
                $tr.addClass('global_setting_heading_section_' + $i);
                $tr.attr('data-counter', $i);
                $tr.attr('data-is-closed', 0);

            }

        });

        $form_table.on('click', 'a.setting_heading_toggle', function (evt) {
            evt.preventDefault();

            var $this      = $(this);
            var $parent    = $this.closest('.global_setting_heading_section');
            var $counter   = Number($parent.data('counter'));
            var $is_closed = Number($parent.data('is-closed'));

            if ($is_closed === 0) {
                $parent.data('is-closed', 1);
                $parent.addClass('global_setting_heading_section_closed');
                $('.global_setting_common_section_' + $counter).hide();
            } else {
                $parent.data('is-closed', 0);
                $parent.removeClass('global_setting_heading_section_closed');
                $('.global_setting_common_section_' + $counter).show();
            }
        });
    });

    /*$('.global_setting_group').each(function (index, element) {
        var $element    = $(element);
        var $form_table = $element.find('.form-table');
        $form_table.prev('h2').remove();
    });*/


    $('.multicheck_fields_sortable').sortable({
        vertical: true,
        handle: '.multicheck_field_handle',
        containerSelector: '.multicheck_fields',
        itemSelector: '.multicheck_field',
        //placeholder      : '<p class="multicheck_field_placeholder"/>',
        placeholder: 'multicheck_field_placeholder'
    });

    $('.global_setting_group').on('click', '.checkbox', function () {
        var mainParent = $(this).closest('.checkbox-toggle-btn');
        if ($(mainParent).find('input.checkbox').is(':checked')) {
            $(mainParent).addClass('active');
        } else {
            $(mainParent).removeClass('active');
        }
    });


    //one click save setting for the current tab
    $('#save_settings').on('click', function (e) {
        e.preventDefault();

        var $current_tab = $('.nav-tab.nav-tab-active');
        var $tab_id      = $current_tab.data('tabid');
        $('#' + $tab_id).find('.submit_hoodslyhub').trigger('click');
    });
});//end dom ready

document.addEventListener('DOMContentLoaded', function () {
    var rl_sandbox_api          = document.getElementsByName("HoodslyHub_rlapi_setting[rl_sandbox_api]");
    var rl_sandbox_api_endpoint = document.querySelector('.rl_sandbox_api_endpoint')
    var rl_api_endpoint         = document.querySelector('.rl_api_endpoint')

    rl_sandbox_api.forEach(e => {
        var radioValue = document.querySelector('.rl_sandbox_api:checked').value;
        if (radioValue === '1') {
            rl_api_endpoint.classList.add('setting-display-none')
        } else if (radioValue === '0') {
            rl_sandbox_api_endpoint.classList.add('setting-display-none')
        }
        e.addEventListener('click', (el) => {
            if (el.target.value === '1') {
                console.log("hello")
                rl_sandbox_api_endpoint.classList.remove('setting-display-none')
                rl_api_endpoint.classList.add('setting-display-none')
            } else if (el.target.value === '0') {
                console.log("No Hello")
                rl_sandbox_api_endpoint.classList.add('setting-display-none')
                rl_api_endpoint.classList.remove('setting-display-none')
            }
        })
    })


});


