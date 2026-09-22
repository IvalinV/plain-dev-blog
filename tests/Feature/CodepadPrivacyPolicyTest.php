<?php

beforeEach(function () {
    $this->withoutVite();
});

it('renders the Codepad privacy policy', function () {
    $this->get(route('apps.codepad.privacy'))
        ->assertSuccessful()
        ->assertSee('Privacy Policy')
        ->assertSee('Codepad')
        ->assertSee('1. Information We Collect')
        ->assertSee('ivalinvenkov@gmail.com');
});
