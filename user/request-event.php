<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../config.php');

$name  = $_SESSION['name'] ?? '';
$email = $_SESSION['email'] ?? '';
$success = isset($_GET['success']) && $_GET['success'] === 'true';
?>

<div
    x-data="{ showSuccessModal: <?= $success ? 'true' : 'false' ?> }"
    x-init="if (showSuccessModal) { setTimeout(() => { showSuccessModal = false; }, 4000); }"
    class="max-w-3xl mx-auto px-6">

    <!-- Success Modal -->
    <div x-show="showSuccessModal" x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        style="display: none;">
        <div class="relative bg-green-100 text-green-800 px-6 py-4 rounded shadow-lg max-w-md text-center font-semibold">
            Your event request has been sent and is awaiting admin approval.
            <button @click="showSuccessModal = false"
                class="absolute top-2 right-2 text-green-800 hover:text-green-900 font-bold text-xl leading-none"
                title="Close">&times;</button>
        </div>
    </div>

    <form
        action="submit-request.php"
        method="POST"
        enctype="multipart/form-data"
        class="bg-white w-full max-w-[750px] p-5 rounded-xl shadow-md border overflow-auto max-h-[600px] overflow-y-auto"
        style="border-color: #1D503A;">

        <div class="mb-1">
            <label for="title" class="block text-gray-700 font-medium">Event Title</label>
            <input type="text" name="title" id="title" required
                class="w-full mt-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#1D503A]" />
        </div>

        <div class="mb-1">
            <label for="description" class="block text-gray-700 font-medium">Description</label>
            <textarea name="description" id="description" rows="4" required
                class="w-full mt-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#1D503A]"></textarea>
        </div>

        <div class="mb-1">
            <label for="event_date" class="block text-gray-700 font-medium">Event Date</label>
            <input type="date" name="event_date" id="event_date" required
                class="w-full mt-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#1D503A]" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="course" class="block text-gray-700">Course</label>
                <input type="text" name="course" id="course" required
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-[#1D503A]" />
            </div>
            <div>
                <label for="year" class="block text-gray-700">Year</label>
                <input type="text" name="year" id="year" required
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-[#1D503A]" />
            </div>
            <div>
                <label for="block" class="block text-gray-700">Block</label>
                <input type="text" name="block" id="block" required
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-[#1D503A]" />
            </div>
        </div>

        <div class="mb-1">
            <label for="request_file" class="block text-gray-700 font-medium mb-1">Attach Request Form (PDF/Image)</label>
            <div class="flex items-center gap-4">
                <div class="flex-grow">
                    <input type="file" name="request_file" id="request_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0 file:text-sm file:font-semibold
                file:bg-[#1D503A] file:text-white hover:file:bg-[#144124]" required />
                </div>

                <button type="submit"
                    class="ml-auto bg-[#1D503A] text-white px-6 py-2 rounded-md hover:bg-[#144124] transition">
                    Submit Request
                </button>
            </div>
        </div>

    </form>
</div>