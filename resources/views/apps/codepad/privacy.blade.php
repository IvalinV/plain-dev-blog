@extends('layouts.blog')

@section('content')
    <article class="wrap-break-words leading-relaxed [&_a]:text-amber-600 [&_a]:underline dark:[&_a]:text-amber-400 [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold sm:[&_h2]:text-2xl">
        <header>
            <h1 class="text-2xl font-bold sm:text-3xl">Privacy Policy</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                <strong>Codepad</strong> · Last updated:
                <time datetime="2026-09-21">September 21, 2026</time>
            </p>
        </header>

        <p class="mt-6">
            This Privacy Policy describes how Codepad ("the app"), developed by Ivalin Venkov
            ("we," "us," or "the developer"), handles information in connection with your use
            of the app. This policy applies to the Codepad app available on Google Play.
        </p>

        <p class="mt-4">
            <strong>In short:</strong> Codepad does not currently collect, store, transmit, or
            share any personal or sensitive user data. There is no account creation, and no
            third-party advertising, analytics, or tracking SDKs are integrated into the app.
        </p>

        <section>
            <h2>1. Information We Collect</h2>
            <p class="mt-4">
                Codepad does not require you to create an account and does not knowingly collect
                personally identifiable information (such as your name, email address, or phone
                number), device identifiers, location data, contacts, photos, or any other
                personal or sensitive user data.
            </p>
            <p class="mt-4">
                The app does not integrate third-party advertising networks, analytics services,
                or crash-reporting SDKs that transmit data off your device. Any data the app uses
                (for example, code or text you enter) stays on your device and is not sent to us
                or to any third party.
            </p>
        </section>

        <section>
            <h2>2. Permissions</h2>
            <p class="mt-4">
                If a future version of Codepad requests a device permission (such as storage
                access to save or open files locally), that permission will only be used to
                provide the corresponding in-app feature, and no data accessed through it will be
                transmitted off your device unless this policy is updated to disclose otherwise
                in advance.
            </p>
        </section>

        <section>
            <h2>3. Third-Party Sharing</h2>
            <p class="mt-4">
                We do not sell, rent, or share any user data with third parties, because the app
                does not collect any user data to begin with.
            </p>
        </section>

        <section>
            <h2>4. Data Security</h2>
            <p class="mt-4">
                Because Codepad does not transmit user data to any server, there is no user data at
                risk in transit or in storage on our systems. Any content you create within the app
                is stored locally on your device, and its security is subject to your device's own
                operating system protections.
            </p>
        </section>

        <section>
            <h2>5. Data Retention &amp; Deletion</h2>
            <p class="mt-4">
                Since Codepad does not collect or store user data on any server, there is no data
                for us to retain or delete on our end. Uninstalling the app removes any locally
                stored content from your device.
            </p>
        </section>

        <section>
            <h2>6. Children's Privacy</h2>
            <p class="mt-4">
                Codepad is not directed at children under the age of 13, and we do not knowingly
                collect any information from children. As noted above, the app does not collect
                personal data from any user, regardless of age.
            </p>
        </section>

        <section>
            <h2>7. Changes to This Policy</h2>
            <p class="mt-4">
                If Codepad's data practices change in the future (for example, if new features
                begin collecting or transmitting data), this Privacy Policy will be updated
                accordingly, and the "Last updated" date above will reflect the most recent
                revision. We encourage you to review this page periodically.
            </p>
        </section>

        <section>
            <h2>8. Contact Us</h2>
            <p class="mt-4">
                If you have any questions or concerns about this Privacy Policy or Codepad's data
                practices, please contact:
            </p>
            <address class="mt-4 not-italic">
                Ivalin Venkov<br>
                <a href="mailto:ivalinvenkov@gmail.com">ivalinvenkov@gmail.com</a>
            </address>
        </section>

        <p class="mt-8">
            This page is published as the official Privacy Policy for the Codepad app on Google
            Play.
        </p>
    </article>
@endsection
