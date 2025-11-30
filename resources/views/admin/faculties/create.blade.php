<div x-cloak
     x-show="openFacultyModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-init="$watch('openFacultyModal', value => document.body.style.overflow = value ? 'hidden' : '')"
     @keydown.escape.window="openFacultyModal = false"
     class="fixed inset-0 flex justify-center items-center z-50 p-4 overflow-hidden"
     style="background-color: rgba(0, 0, 0, 0.4);"> <!-- dark overlay -->

    <!-- Glass Modal Container -->
    <div @click.away="openFacultyModal = false"
         class="backdrop-blur-lg rounded-3xl border border-white/20 shadow-2xl
                w-full max-w-5xl max-h-[90vh] overflow-y-auto scrollbar-hide flex flex-col p-10"
         style="background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2);"> <!-- white frosted glass -->

        <!-- Header -->
        <div class="flex justify-between items-center mb-6 flex-shrink-0">
            <h2 id="addFacultyTitle" class="text-2xl font-bold text-black">Add New Faculty</h2>
            <button type="button" @click="openFacultyModal = false"
                    class="text-black text-3xl leading-none" aria-label="Close modal">&times;</button>
        </div>

        <!-- Scrollable Form -->
        <form action="{{ route('admin.faculties.store') }}" method="POST"
              class="space-y-10 overflow-y-auto scrollbar-hide max-h-[70vh]" novalidate>
            @csrf

            <!-- Personal Information -->
            <div>
                <h3 class="text-xl font-semibold mb-3 flex items-center gap-2 text-black/80">
                    <i class="fas fa-user text-black/70"></i> Personal Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Full Name *</label>
                        <input type="text" name="name" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Subject *</label>
                        <input type="text" name="course_subject" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Hours -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Hours *</label>
                        <input type="number" name="no_of_hours" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Units -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Units *</label>
                        <input type="number" name="units" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Students -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Students *</label>
                        <input type="number" name="no_of_students" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Section -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Section *</label>
                        <input type="text" name="year_section" required
                               class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                               style="background-color: rgba(255, 255, 255, 0.6);">
                    </div>

                    <!-- Action Type -->
                    <div>
                        <label class="text-black text-sm mb-1 block">Action Type</label>
                        <select name="action_type"
                                class="w-full p-4 rounded-xl border border-white/20 text-black placeholder-black/50 focus:ring-2 focus:ring-[#6D9773]"
                                style="background-color: rgba(255, 255, 255, 0.6);">
                            <option value="Lecture">Lecture</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Examination">Examination</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4 flex-shrink-0">
                <!-- Cancel -->
                <button type="button"
                        @click="openFacultyModal = false"
                        class="px-6 py-3 rounded-xl text-black transition-colors duration-300"
                        style="background-color: rgba(0,0,0,0.05);"
                        onmouseover="this.style.backgroundColor='#ebdb4a70'"
                        onmouseout="this.style.backgroundColor='#b3a74063'">
                    Cancel
                </button>

                <!-- Add -->
                <button type="submit"
                        class="px-6 py-3 rounded-xl font-semibold text-white transition-colors duration-300"
                        style="background-color: #6D9773;"
                        onmouseover="this.style.backgroundColor='#dac611da'"
                        onmouseout="this.style.backgroundColor='#ede17263'">
                    Add Faculty
                </button>
            </div>

        </form>
    </div>
</div>
