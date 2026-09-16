<div class="w-full py-16 px-4 flex justify-center">
    <div class="max-w-md w-full text-center">
        <h3 class="text-gray-500 dark:text-slate-200 font-medium mb-1">Subscribe to the newsletter</h3>
        <p id="info-message" class="text-gray-500 dark:text-slate-400 text-sm mb-4">Get new posts delivered to your inbox.
        </p>

        <form class="flex gap-2" id="subscribe-form">
            <input type="email" name="email" placeholder="you@example.com" required
                class="flex-1 bg-white text-gray-500 dark:bg-slate-900 border border-slate-700 rounded-lg px-3 py-2
               dark:text-slate-200 placeholder-slate-500 text-sm
               focus:outline-none focus:ring-1 focus:ring-slate-500 focus:border-slate-500" />
            <button type="submit"
                class="bg-white hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-700 dark:text-slate-200 text-gray-500
               text-sm font-medium rounded-lg px-4 py-2 transition-colors cursor-pointer">
                Subscribe
            </button>
        </form>
    </div>
</div>

<script>
    document.getElementById('subscribe-form').addEventListener('submit', async (e) => {
        e.preventDefault(); // stop the page from reloading
        const email = e.target.email.value;

        try {
            const res = await fetch('/api/newsletter/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email
                })
            });

            const el = document.getElementById('info-message')

            const data = await res.json()

            if (!res.ok) {

                el.textContent = data.message
                el.style.color = "red"

                throw new Error('Subscription failed')
            };
            // success — swap in a confirmation message, clear the input, etc.

            el.textContent = data.message
            el.style.color = "#d97706"

            e.target.reset();
        } catch (err) {
            console.error(err);
            const el = document.getElementById('info-message')

            // show an error state to the user

        }
    });
</script>
