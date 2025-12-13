@extends('layouts.app')

@section('content')
    <h3>Customer</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="" class="btn btn-primary">Create</a>
        </div>
        <div class="col-md-6 text-right">
            <div class="input-group" style="width: 200px; float: right;">
                <input type="text" class="form-control form-control-sm" placeholder="Search" style="border-right: none;">
                <div class="input-group-append">
                    <span class="input-group-text" style="background: white; border-left: none; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Image</th>
                <th>Name</th>
                <th>Title</th>
                <th>Company</th>
                <th>Position</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Address</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td colspan="9" class="text-center">Tidak ada data customer</td>
            </tr>
        </tbody>
    </table>
@endsection
