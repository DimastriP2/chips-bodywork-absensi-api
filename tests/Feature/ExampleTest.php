<?php

test('landing page redirects visitors to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
