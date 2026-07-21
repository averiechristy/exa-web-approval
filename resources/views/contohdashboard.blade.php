@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="row mb-4">

    <div class="col-md-3">
        <div class="card border-left-primary shadow py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary">Total Documents</div>
                <div class="h5 font-weight-bold">120</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-warning shadow py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning">Pending</div>
                <div class="h5 font-weight-bold">45</div>
            </div>
        </div>
    </div>

</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <b>Document Tracking (SLA)</b>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Current Step</th>
                    <th>SLA</th>
                    <th>Elapsed</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Contract A</td>
                    <td>Manager</td>
                    <td>2 Days</td>
                    <td>1 Day</td>
                    <td><span class="badge badge-success">On Track</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection