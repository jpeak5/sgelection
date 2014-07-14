<?php

function block_sgelection_images() {
    return array(html_writer::tag('img', '', array('alt' => get_string('red', 'block_sgelection'), 'src' => "pix/picture0.gif")),
                 html_writer::tag('img', '', array('alt' => get_string('blue', 'block_sgelection'), 'src' => "pix/picture1.png")),
                 html_writer::tag('img', '', array('alt' => get_string('green', 'block_sgelection'), 'src' => 'pix/picture2.jpeg')));
}