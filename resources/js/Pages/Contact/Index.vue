<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import Footer from "../../Components/Index/Footer.vue";

const page = usePage();

const form = useForm({
    name: "",
    email: "",
    subject: "",
    message: "",
});

const submit = () => {
    form.post(route("contact.store"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contact Us | SiteClip" />

    <!-- Header: logo only -->
    <header class="flex justify-center items-center my-5 md:mx-16 mx-8">
        <div class="logo">
            <Link :href="route('home.index')">
                <img class="h-16" src="/SiteClipLogo.png" alt="SiteClip" />
            </Link>
        </div>
    </header>

    <main>
        <section class="max-w-3xl mx-auto px-6 mb-20 mt-6 lg:mt-16">
            <div class="text-center mb-10">
                <div class="spanText flex justify-center">
                    <span
                        class="inline-block bg-black text-white text-sm px-4 py-1 rounded-full"
                        >Get in touch</span
                    >
                </div>
                <h1 class="font-headingfont text-3xl md:text-5xl mt-4">
                    Contact Us
                </h1>
                <p class="text-gray-500 mt-4 text-lg">
                    Have a question or feedback? Fill out the form below and
                    we'll get back to you shortly.
                </p>
            </div>

            <!-- Success message -->
            <div
                v-if="page.props.flash?.success"
                class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-6 py-4 text-center shadow"
            >
                {{ page.props.flash.success }}
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 md:p-10 space-y-5"
            >
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Name</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Your full name"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-black transition"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-black transition"
                    />
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Subject (optional)</label
                    >
                    <input
                        v-model="form.subject"
                        type="text"
                        placeholder="What's this about?"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-black transition"
                    />
                    <p
                        v-if="form.errors.subject"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors.subject }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Message</label
                    >
                    <textarea
                        v-model="form.message"
                        rows="5"
                        placeholder="Write your message here..."
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-black transition resize-none"
                    ></textarea>
                    <p
                        v-if="form.errors.message"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors.message }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="btn btn-primary w-full justify-center disabled:opacity-50"
                >
                    {{ form.processing ? "Sending..." : "Send Message" }}
                    <span>→</span>
                </button>
            </form>
        </section>
    </main>

    <footer>
        <Footer />
    </footer>
</template>