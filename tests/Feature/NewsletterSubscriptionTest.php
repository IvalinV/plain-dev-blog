<?php

use Spatie\Newsletter\Facades\Newsletter;

it('subscribes a valid email address', function () {
    Newsletter::shouldReceive('subscribe')
        ->once()
        ->with('reader@gmail.com')
        ->andReturn(['id' => 'subscriber-id']);

    $this->postJson('/api/newsletter/subscribe', [
        'email' => 'reader@gmail.com',
    ])
        ->assertSuccessful()
        ->assertJson([
            'message' => 'Email subscribed succesfuly',
        ]);
});

it('rejects an invalid email address', function () {
    $this->postJson('/api/newsletter/subscribe', [
        'email' => 'not-an-email',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns a server error when the newsletter provider rejects the subscription', function () {
    Newsletter::shouldReceive('subscribe')
        ->once()
        ->with('reader@gmail.com')
        ->andReturn(false);

    $this->postJson('/api/newsletter/subscribe', [
        'email' => 'reader@gmail.com',
    ])
        ->assertServerError()
        ->assertJson([
            'message' => 'Something went wrong',
        ]);
});

it('throttles newsletter subscriptions', function () {
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $this->postJson('/api/newsletter/subscribe', [
            'email' => 'not-an-email',
        ])->assertUnprocessable();
    }

    $this->postJson('/api/newsletter/subscribe', [
        'email' => 'not-an-email',
    ])->assertTooManyRequests();
});
