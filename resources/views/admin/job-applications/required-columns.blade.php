<style>
     .required:after {
    content:" *";
    color: red;
  }
</style>
@if (!is_null($job->required_columns))    
    @if ($job->required_columns['gender'])
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('modules.front.gender')</label>
            <div class="flex items-center gap-4">
                @foreach ($gender as $key => $value)
                    <div class="flex items-center">
                        <input @if (!empty($application) && $key == $application->gender) checked @endif class="mr-2" type="radio" name="gender" id="{{ $key }}" value="{{ $key }}">
                        <label class="text-sm text-gray-700" for="{{ $key }}">{{ ucFirst($value) }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if ($job->required_columns['dob'])
        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('modules.front.dob')</label>
            <input class="form-control dob" type="text" name="dob"
                placeholder="@lang('modules.front.dob')" autocomplete="none">
        </div>
    @endif
    @if ($job->required_columns['country'])
        <div class="form-group">
            <h6 class="text-sm font-semibold text-gray-900 mb-3 required">
                @lang('modules.front.country')
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <select class="select2 countries form-control" name="country" id="countryId">
                        <option value="0">@lang('modules.front.selectCountry')</option>
                    </select>
                </div>
                <div class="form-group">
                    <select class="select2 states form-control" name="state" id="stateId">
                        <option value="0">@lang('modules.front.selectState')</option>
                    </select>
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" name="city" id="cityId" placeholder="@lang('modules.front.selectCity')" value="{{ !empty($application) ? $application->city : '' }}">
                </div>
                <div class="form-group">
                    <input class="form-control" type="number" name="zip_code" id="zipCode" placeholder="@lang('modules.front.zipCode')" value="{{ !empty($application) ? $application->zip_code : '' }}">
                </div>
            </div>
        </div>
    @endif
@endif
