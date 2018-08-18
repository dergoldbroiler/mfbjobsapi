jQuery.noConflict();

(function ($) {
    $(function () {

        
        $(document).ready(function () {
           
            $('.searchformresult,.jobskillresult,.licenseresult').html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>').hide();

            /*
             * accordeon panels
             */
            $('.boxtitle').click(function () {
                //  $('.boxcontent').slideUp();
                var nextcontent = $(this).next('.boxcontent').css('display');
                if (nextcontent == "none") {
                    $(this).next('.boxcontent').slideDown();
                } else {
                    $(this).next('.boxcontent').slideUp();
                }
            });

            if ($('#jobbezeichnung_3').val() == "") {
                $('#jobedudegree_3').hide();
            }
            if ($('#jobbezeichnung_2').val() == "") {
                $('#jobedudegree_2').hide();
            }

                     $( ".datepicker" ).datepicker();
                      $( ".datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );

            $('#searchinput_KEY').blur(function () {
                if ($(this).val() == "") {
                    $(this).css('background-color', 'red');

                } else {
                    $(this).css('background-color', 'white');
                }
            })
            $('#jobbezeichnung_2,#jobbezeichnung_3').focus(function () {
                if ($('#searchinput_KEY').val() == "") {
                    $('#searchinput_KEY').css('background-color', 'red');
                    $(this).attr('disabled', true);
                } else {
                    $(this).css('background-color', 'white');
                    $(this).attr('disabled', false);
                }
            })

        });


        /* check for correct job list */
        $('#jobaction').change(function () {
            var errors = "";
            var boolerr = false;

            if ($(this).val() == 1) { // Jobaction Neuanlage
                if ($('.zustand1').html() != "E") {
                    errors += "Beruf 1 muss für Neuanlage geändert werden.\n(Zustand E ist erforderlich)";
                    boolerr = true;
                } else {
                    errors += "";
                    boolerr = false;
                }

                if ($('.zustand2').html() != "E") {
                    errors += "\n\nBeruf 2 muss für Neuanlage geändert werden.\n(Zustand E ist erforderlich)";
                    boolerr = true;
                } else {
                    boolerr = false;
                }

                if ($('.zustand3').html() != "E") {
                    errors += "\n\nBeruf 3 muss für Neuanlage geändert werden.\n(Zustand E ist erforderlich)";
                    boolerr = true;
                } else {
                    boolerr = false;
                }
            }
            if (boolerr == true) {
                alert(errors);
            } else {
                boolerr = false;
            }
        });
        /* check for correct job list */
        $('#joboffertype').change(function () {
            var errors = "";
            var boolerr = false;
            //  data-typ="A" data-quali="2,4"
            $('#searchinput_KEY,#jobbezeichnung_2,#jobbezeichnung_3').val('');
            if ($(this).val() == 38) { // Jobaction Neuanlage
                $('#searchinput_KEY,#jobbezeichnung_2,#jobbezeichnung_3').attr('data-typ', 't');
                $('#searchinput_KEY,#jobbezeichnung_2,#jobbezeichnung_3').attr('data-quali', '1');
            } else {
                $('#searchinput_KEY,#jobbezeichnung_2,#jobbezeichnung_3').attr('data-typ', 't');
                $('#searchinput_KEY,#jobbezeichnung_2,#jobbezeichnung_3').attr('data-quali', 'all');
            }

        });
                /* check for correct job list */
        $('#jobtermbool').change(function () {
           
            if ($(this).val() == 2) { // Jobaction Neuanlage
              $('#jobtermlength,#jobtermdate,#jobtermtakeover').val('');
            }

        });
        $('#jobedudegree').change(function () {

            if ($(this).val() == "1" || $(this).val() == 0) {
                if ($('.hs1').html() != "nein") {
                    alert("Für Beruf 1 muss ein Hochschulabschluss vorliegen");
                }
                if ($('.hs2').html() != "nein") {
                    alert("Für Beruf 2 muss ein Hochschulabschluss vorliegen");
                }
                if ($('.hs3').html() != "nein") {
                    alert("Für Beruf 3 muss ein Hochschulabschluss vorliegen");
                }
            }

            //

        });



        if ($('#jobleadership').val() != 1) {
            $('.joblead').fadeIn();
        } else {
            $('.joblead').fadeOut();
        }

        $('.addrow').click(function (ev) {
            ev.preventDefault();
            $($(this).attr('data-load')).fadeIn();

        })


        $('#jobtermbool').change(function () {
            if ($(this).val() != 2) {
                $('.jobterm').fadeIn();
            } else {
                $('.jobterm').fadeOut();
            }
        })

        $('#jobleadership').change(function () {
            if ($(this).val() != 1) {
                $('.joblead').fadeIn();
            } else {
                $('.joblead').fadeOut();
            }
        })
        $('#jobpayscale').change(function () {

            if ($(this).val() == 1) {
                $('.jobagreement').fadeIn();
            } else {

                $('.jobagreement').fadeOut();
            }
        })
        $('.elmchanger,.joblicense').focus(function () {
            $('.searchformresult,.jobskillresult,.licenseresult').hide();
            $('.' + $(this).attr('data-result')).fadeIn();
        });
         
        
        
         $('.searchformresult,.jobskillresult,.licenseresult').html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>').hide();
        $('.elmchanger').keyup(function () {
            $('.' + $(this).attr('data-result')).css('display', 'block');
            
            //alert($(this).html())
            var current_input_length = $(this).val().length;
            var current_input = $(this).val();
            var suggest_url = $(this).attr('data-suggest');
            var resultdiv = $(this).attr('data-result');
            var job_title = $(this).attr('data-job-titlelm');
            var numberof = $(this).attr('data-num');
            var allowed_type = $(this).attr('data-typ');
            var allowed_quali = $(this).attr('data-quali');

            if (current_input_length > 3) {



                $.post(suggest_url, {
                        searchquery: current_input,
                        searchtype: "job",
                        resultdiv: resultdiv,
                        titlelm: "." + job_title,
                        numberof: numberof,
                        allowed_type: allowed_type,
                        allowed_quali: allowed_quali
                    })

                    .done(function (data) {

                        console.log("Data Loaded: " + data);

                        jQuery('.' + resultdiv).html(data);

                    });



            } else {
                jQuery('.' + resultdiv).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }


        });
        
        $('.joblicense').keyup(function () {
            $('.' + $(this).attr('data-result')).css('display', 'block');
           // $('.' + $(this).attr('data-result')).css('border', '1px solid red');
            console.log($(this).val().length);
            var current_input_length = $(this).val().length;
            var current_input = $(this).val();
            var license_result = $(this).attr('data-result'); // resultate laden in diesen div
            var suggest_url = $(this).attr('data-suggest');
            var resultdiv = $(this).attr('data-result');
            var titlelm = $(this).attr('id');


            if (current_input_length > 2) {

                $.post(suggest_url, {
                    searchquery: current_input,
                    searchtype: "licenses",
                    resultdiv: resultdiv,
                    titlelm: '#'+titlelm
                }).done(function (data) {

                   console.log("Data Loaded: " + data);
                    $('.' + resultdiv).html(data);
                });
            } else {
                jQuery('.' + resultdiv).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }
        });

        $('#jobknowledge').keyup(function () {

            var current_input_length = $(this).val().length;
            var current_input = $(this).val();
            var suggest_url = $(this).attr('data-suggest');
            var resultdiv = $(this).attr('data-result');
            var allowed_type = $(this).attr('data-typ');
            var allowed_quali = $(this).attr('data-quali');
            var titlelm = $(this).attr('data-job-titlelm');
            //s   var mainjobid
            if (current_input_length > 3) {
                $.post(suggest_url, {
                        searchquery: current_input,
                        searchtype: "learning",
                        resultdiv: resultdiv,
                        allowed_type: allowed_type,
                        allowed_quali: allowed_quali,
                        titlelm: titlelm
                    })

                    .done(function (data) {

                        console.log("Data Loaded: " + data);

                        jQuery('.' + resultdiv).html(data);

                    });



            } else {
                jQuery('.' + resultdiv).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }


        });





        $('#jobeduname').keyup(function () {
            var current_input_length = $(this).val().length;
            var current_input = $(this).val();
            var suggest_url = $(this).attr('data-suggest');
            if (current_input_length > 3) {

                $.post(suggest_url, {
                        searchquery: current_input,
                        searchtype: "job"
                    })

                    .done(function (data) {

                        console.log("Data Loaded: " + data);

                        jQuery('.searchformresult2').html(data);

                    });



            } else {
                jQuery('.searchformresult2').html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }

        });




        $('.driveskills').focus(function () {
            console.log($(this).attr('data-skill-result'))

            var skill_result = $(this).attr('data-skill-result');
            $('.' + skill_result).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>').css('display', 'block').fadeIn();
            var skill_field_id = $(this).attr('id');
            //var skill_name = $(this).attr('data-skill-name');
            var current_input_length = $("#" + skill_field_id).val().length;
            var current_input = $("#" + skill_field_id).val();
            var suggest_url = $("#" + skill_field_id).attr('data-suggest');
            var datakey = $("#" + skill_field_id).attr('data-key');
            if (current_input_length > 1) {
                $.post(suggest_url, {
                        searchquery: datakey,
                        searchtype: "skill",
                        resultdiv: "#" + skill_field_id
                    })

                    .done(function (data) {

                        console.log("Data Loaded: " + data);
                        $('.' + skill_result).html(data);

                    });



            } else {
                $('.' + skill_result).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }
        });
        $('.jobskills').keyup(function () {
            console.log($(this).attr('data-skill-result'))

            var skill_result = $(this).attr('data-skill-result');
            $('.' + skill_result).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>').css('display', 'block').fadeIn();
            var skill_result = $(this).attr('data-skill-result');
            var skill_field_id = $(this).attr('id');
            //var skill_name = $(this).attr('data-skill-name');
            var current_input_length = $("#" + skill_field_id).val().length;
            var current_input = $("#" + skill_field_id).val();
            var suggest_url = $("#" + skill_field_id).attr('data-suggest');

            if (current_input_length > 3) {

                $.post(suggest_url, {
                        searchquery: current_input,
                        searchtype: "skill",
                        resultdiv: "#" + skill_field_id
                    })

                    .done(function (data) {

                        console.log("Data Loaded: " + data);
                        jQuery('.' + skill_result).html(data);

                    });



            } else {
                $('.' + skill_result).html('<img src=../wp-content/plugins/mfbjobsapi/assets/img/ajax-loader.gif>');
            }

        });


    });
})(jQuery);