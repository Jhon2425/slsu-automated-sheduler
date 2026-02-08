<x-app-layout>
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4">MINIMAL TEST FORM</h1>
        
        {{-- Show debug info --}}
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400">
            <h2 class="font-bold">Debug Information:</h2>
            <p>User ID: {{ $user->id ?? 'NOT SET' }}</p>
            <p>User Name: {{ $user->name ?? 'NOT SET' }}</p>
            <p>Faculty ID: {{ $faculty->id ?? 'NOT SET' }}</p>
            <p>Faculty Name: {{ $faculty->name ?? 'NOT SET' }}</p>
            <hr class="my-2">
            <p>Edit Route: 
                @if(isset($user))
                    {{ route('admin.faculty.edit', $user->id) }}
                @else
                    <span class="text-red-600">Cannot generate - $user not set</span>
                @endif
            </p>
            <p>Update Route: 
                @if(isset($user))
                    {{ route('admin.faculty.update', $user->id) }}
                @else
                    <span class="text-red-600">Cannot generate - $user not set</span>
                @endif
            </p>
        </div>

        {{-- Minimal form --}}
        <form action="{{ route('admin.faculty.update', $user->id) }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Name:</label>
                <input type="text" name="name" value="{{ old('name', $faculty->name) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Email:</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Civil Status:</label>
                <select name="civil_status" class="border p-2 w-full" required>
                    <option value="Single" {{ old('civil_status', $faculty->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
                    <option value="Married" {{ old('civil_status', $faculty->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Birthdate:</label>
                <input type="date" name="birthdate" value="{{ old('birthdate', $faculty->birthdate) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Employment Status:</label>
                <input type="text" name="employment_status" value="{{ old('employment_status', $faculty->employment_status) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Home Address:</label>
                <textarea name="home_address" class="border p-2 w-full" required>{{ old('home_address', $faculty->home_address) }}</textarea>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Degree Earned:</label>
                <input type="text" name="degree_earned" value="{{ old('degree_earned', $faculty->degree_earned) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Year Graduated:</label>
                <input type="number" name="year_graduated" value="{{ old('year_graduated', $faculty->year_graduated) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">Course:</label>
                <input type="text" name="course" value="{{ old('course', $faculty->course) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <div class="mb-4">
                <label class="block font-bold mb-2">School Graduated:</label>
                <input type="text" name="school_graduated" value="{{ old('school_graduated', $faculty->school_graduated) }}" 
                       class="border p-2 w-full" required>
            </div>
            
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded">
                TEST UPDATE
            </button>
            
            <a href="{{ route('admin.faculty.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded ml-2 inline-block">
                Cancel
            </a>
        </form>
        
        <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT DEBUG ===');
            console.log('Action:', this.action);
            console.log('Method:', this.method);
            
            const formData = new FormData(this);
            console.log('_method:', formData.get('_method'));
            console.log('_token:', formData.get('_token') ? 'Present' : 'MISSING');
            console.log('name:', formData.get('name'));
            console.log('=== END DEBUG ===');
        });
        </script>
    </div>
</x-app-layout>