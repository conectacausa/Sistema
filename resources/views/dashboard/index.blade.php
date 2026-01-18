@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-xxl-8">
            <div class="box rounded-4 b-1">
                <div class="box-header b-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="">Total Sale</h3>
                        <h1 class="mb-0 mt-20 fw-500">$1,950.86</h1>
                        <p class="text-fade">Yeah! your sales have surged by $723.12 from last month!</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-outline btn-sm rounded-pill dropdown-toggle" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            Last 7 Days
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Daily</a>
                            <a class="dropdown-item" href="#">This Weekly</a>
                            <a class="dropdown-item" href="#">This Yearly</a>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-xxl-4 col-lg-4 col-md-12 col-12">
                            <div class="box rounded-4 pull-up" style="background: #F1B5B9;">
                                <div class="box-body">
                                    <div>
                                        <p class="mt-0 mb-5 fw-400 text-black-50 fs-18">In-Store Sales</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h3 class="m-0 fw-500 text-black">$5,128.90</h3>
                                            <span class="badge bg-white-50 badge-pill text-success">
                                                <span class="feather-arrow-up"></span> +25%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-lg-4 col-md-12 col-12">
                            <div class="box rounded-4 pull-up" style="background: #97D8D0">
                                <div class="box-body">
                                    <div>
                                        <p class="mt-0 mb-5 fw-400 text-black-50 fs-18">Online Sales</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h3 class="m-0 fw-500 text-black">$2,154.10</h3>
                                            <span class="badge bg-white-50 badge-pill text-danger">
                                                <span class="feather-arrow-down"></span> -1%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-4 col-lg-4 col-md-12 col-12">
                            <div class="box rounded-4 pull-up" style="background: #D7F1B5;">
                                <div class="box-body">
                                    <div>
                                        <p class="mt-0 mb-5 fw-400 text-black-50 fs-18">Total Sales</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h3 class="m-0 fw-500 text-black">$7,450.5</h3>
                                            <span class="badge bg-white-50 badge-pill text-success">
                                                <span class="feather-arrow-up"></span> +15%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4">
            <div class="box overflow-hidden rounded-4 b-1">
                <div>
                    <div class="box-body pb-0 d-flex align-items-center justify-content-between">
                        <h3 class="m-0">Total Order</h3>
                        <i class="ti-bar-chart d-block w-40 h-40 bg-lighter rounded-circle align-content-center text-center mb-10"></i>
                    </div>
                    <div class="box-body pt-0">
                        <h2 class="m-0 fw-500">$564
                            <span class="badge badge-success-light badge-pill fs-12 pb-0">
                                <span class="feather-arrow-up fs-12"></span> +15%
                            </span>
                        </h2>
                        <p class="m-0 text-fade">Your order have surged by 25 from last month!</p>
                    </div>
                    <div id="chart-widget1"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="box b-1 rounded-4">
                <div class="box-header b-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="m-0 fw-500">Revenue By Category</h3>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-secondary btn-outline btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Monthly
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Daily</a>
                                <a class="dropdown-item" href="#">Weekly</a>
                                <a class="dropdown-item" href="#">Yearly</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div id="balance-overview"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-7 col-xl-7">
            <div class="box rounded-4 b-1">
                <div class="box-header b-0 pb-0 d-flex justify-content-between align-items-center">
                    <h3 class="m-0 fw-500">Monthly Sales Performance</h3>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-outline btn-sm rounded-pill dropdown-toggle" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                            This Year
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">Today</a>
                            <a class="dropdown-item" href="#">This Week</a>
                            <a class="dropdown-item" href="#">This Month</a>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div id="chart-Overall"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-12 col-lg-12 col-12">
            <div class="box rounded-4 b-1">
                <div class="box-header no-border pb-0">
                    <h3 class="m-0 fw-500">Top Selling Products</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-hover table-bordered text-nowrap">
                            <thead class="bg-light mt-5">
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>#ORD121</td>
                                <td>Nullacin</td>
                                <td>120 Units</td>
                                <td>$2.08</td>
                                <td><span class="badge badge-pill badge-success-light">In stock</span></td>
                                <td class="d-flex">
                                    <a href="#" class="w-30 h-30 l-h-32 bg-primary hover-white d-block text-center align-content-center rounded me-2">
                                        <i class="feather-eye fs-16" aria-hidden="true"></i>
                                    </a>
                                    <a href="#" class="w-30 h-30 l-h-32 bg-danger hover-white d-block text-center align-content-center rounded me-2">
                                        <i class="feather-trash-2 fs-16" aria-hidden="true"></i>
                                    </a>
                                    <a href="#" class="w-30 h-30 l-h-32 bg-success hover-white d-block text-center align-content-center rounded me-2">
                                        <i class="fa fa-share fs-16" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
    {{-- Script do dashboard do template --}}
    <script src="{{ asset('assets/js/pages/dashboard3.js') }}"></script>

    {{-- Se quiser inicializar o DataTables do exemplo --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.DataTable && document.getElementById('example')) {
                jQuery('#example').DataTable();
            }
        });
    </script>
@endpush
