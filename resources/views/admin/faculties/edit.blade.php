<div x-cloak
     x-show="openEditModal"
     x-transition
     class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-start pt-10 z-50"
     @keydown.escape.window="openEditModal = false">

    <div @click.away="openEditModal = false"
         class="bg-gray-900/90 backdrop-blur-xl p-10 rounded-3xl border border-gray-700 shadow-2xl
                w-full max-w-5xl max-h-[90vh] overflow-y-auto scrollbar-hide">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Edit Faculty</h2>
            <button type="button" @click="openEditModal = false"
                    class="text-white text-3xl leading-none">&times;</button>
        </div>

        <!-- Form -->
        <form method="POST" 
            :action="`/admin/faculties/${editFaculty.id}`"
            class="space-y-10">

            @csrf
            @method('PUT')

            <!-- Personal Info -->
            <div>
                <h3 class="text-xl font-semibold text-white mb-3 flex items-center gap-2">
                    <i class="fas fa-user text-yellow-400"></i> Personal Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="text-white text-sm mb-1 block">Full Name *</label>
                        <input type="text" name="name"
                               x-model="editFaculty.name"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 
                               text-white placeholder-gray-400">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Subject *</label>
                        <input type="text" name="course_subject"
                               x-model="editFaculty.course_subject"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Hours *</label>
                        <input type="number" name="no_of_hours"
                               x-model="editFaculty.no_of_hours"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Units *</label>
                        <input type="number" name="units"
                               x-model="editFaculty.units"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Students *</label>
                        <input type="number" name="no_of_students"
                               x-model="editFaculty.no_of_students"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Section *</label>
                        <input type="text" name="year_section"
                               x-model="editFaculty.year_section"
                               required
                               class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                    </div>

                    <div>
                        <label class="text-white text-sm mb-1 block">Action Type</label>
                        <select name="action_type"
                            x-model="editFaculty.action_type"
                            class="w-full p-4 rounded-xl bg-gray-800 border border-gray-600 text-white">
                            <option value="Lecture">Lecture</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Examination">Examination</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-4">
                <button type="button"
                        @click="openEditModal = false"
                        class="px-6 py-3 bg-gray-700 text-white rounded-xl">
                    Cancel
                </button>

                <button type="submit"
                        class="px-6 py-3 bg-yellow-400 text-black rounded-xl font-semibold">
                    Update Faculty
                </button>
            </div>

        </form>
    </div>
</div>
