<?php
add_action("rest_api_init", function(){ register_rest_route("mb-qa/v1","/ping", ["methods"=>"GET","callback"=>function(){ return ["ok"=>true, "ts"=>time()]; }, "permission_callback"=>"__return_true", ]); });
add_action("wp_footer", function(){ echo "\n<!-- MB_FIXER_ACTIVE -->\n"; }, 999);
function mb_qa_echo_handler(){ error_log("[MB-QA] AJAX action=mb_qa_echo"); wp_send_json_success(["pong"=>time()]); }
add_action("wp_ajax_mb_qa_echo","mb_qa_echo_handler");
add_action("wp_ajax_nopriv_mb_qa_echo","mb_qa_echo_handler");
