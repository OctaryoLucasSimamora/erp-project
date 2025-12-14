@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Customer - {{ $customer->name }}</h3>
        <a href="{{ route('sales.customer.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please check the form below.
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('sales.customer.update', $customer->id) }}" method="POST" enctype="multipart/form-data" id="customer-form">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $customer->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="title" id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $customer->title) }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <input type="text" name="company" id="company" 
                                           class="form-control @error('company') is-invalid @enderror" 
                                           value="{{ old('company', $customer->company) }}">
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" name="position" id="position" 
                                           class="form-control @error('position') is-invalid @enderror" 
                                           value="{{ old('position', $customer->position) }}">
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="image">Profile Image</label>
                            
                            <!-- Current Image -->
                            @if($customer->image)
                                <div class="mb-2" id="current-image-container">
                                    <img src="{{ asset('storage/' . $customer->image) }}" 
                                         alt="{{ $customer->name }}" 
                                         class="img-thumbnail d-block mx-auto" 
                                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="remove_image" id="remove_image" 
                                               class="form-check-input" value="1">
                                        <label class="form-check-label text-danger" for="remove_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Upload New Image -->
                            <div class="custom-file">
                                <input type="file" name="image" id="image" 
                                       class="custom-file-input @error('image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                <label class="custom-file-label" for="image">Choose new image...</label>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Max size: 2MB. Formats: jpeg, png, jpg, gif</small>
                            
                            <!-- Preview New Image -->
                            <div id="image-preview" class="mt-2 text-center" style="display: none;">
                                <img id="preview-image" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" name="mobile" id="mobile" 
                                   class="form-control @error('mobile') is-invalid @enderror" 
                                   value="{{ old('mobile', $customer->mobile) }}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3" 
                              class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.customer.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Customer
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview for new upload
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (2MB max)
            if (file.size > 2048000) {
                alert('File size must be less than 2MB');
                this.value = '';
                $('#image-preview').hide();
                $('.custom-file-label').text('Choose new image...');
                return;
            }

            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG, PNG, JPG, and GIF files are allowed');
                this.value = '';
                $('#image-preview').hide();
                $('.custom-file-label').text('Choose new image...');
                return;
            }

            const reader = new FileReader();
            
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#image-preview').show();
                // Hide current image when previewing new one
                $('#current-image-container').hide();
            }
            
            reader.readAsDataURL(file);
            $('.custom-file-label').text(file.name);
        } else {
            $('#image-preview').hide();
            $('.custom-file-label').text('Choose new image...');
            $('#current-image-container').show();
        }
    });

    // Handle remove image checkbox
    $('#remove_image').on('change', function() {
        if (this.checked) {
            $('#current-image-container img').css('opacity', '0.3');
            // Clear file input and preview
            $('#image').val('');
            $('#image-preview').hide();
            $('.custom-file-label').text('Choose new image...');
        } else {
            $('#current-image-container img').css('opacity', '1');
        }
    });

    // Reset current image display when file input is cleared
    $('#image').on('click', function() {
        if (!this.value) {
            $('#current-image-container').show();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
    
    .img-thumbnail {
        border: 2px solid #dee2e6;
    }
</style>
@endpush