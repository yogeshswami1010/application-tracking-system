<form id="createCompanyForm">

    @csrf

    <div class="space-y-5 p-5">

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Company Name
            </label>

            <input
                type="text"
                name="company_name"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-[#2563EB] focus:outline-none"
                placeholder="Enter company name">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Website
            </label>

            <input
                type="text"
                name="website"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-[#2563EB] focus:outline-none"
                placeholder="https://example.com">
        </div>

        <div class="flex justify-end gap-3">

            <button
                type="button"
                onclick="closeJobAuxModal()"
                class="rounded-xl border border-gray-300 px-5 py-2.5">

                Cancel
            </button>

            <button
                type="submit"
                class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-white">

                Save Company
            </button>

        </div>

    </div>

</form>

<script>

$('#createCompanyForm').on('submit', function(e)
{
    e.preventDefault();

    $.ajax({

        url: "{{ route('admin.company.store') }}",
        type: "POST",
        data: $(this).serialize(),

        success: function(response)
        {
            $('#company').append(`
                <option value="${response.id}" selected>
                    ${response.company_name}
                </option>
            `);

            $('#company').trigger('change');

            closeJobAuxModal();
        },

        error: function(xhr)
        {
            alert('Something went wrong');
        }

    });
});

</script>