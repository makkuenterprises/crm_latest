@extends('admin.layouts.app')


@section('main-content')


<!--**********************************
            Content body start
        ***********************************-->
		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row page-titles">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('admin.view.group.list') }}">Data Records</a></li>
						<li class="breadcrumb-item active"><a href="{{ route('admin.view.group.list') }}">Data Groups</a></li>
					</ol>
                </div>
				<!-- Row -->
				<div class="row">
					<div class="col-xl-12">
						<div>
							<a href="{{ route('admin.view.group.create') }}" type="button" class="btn btn-sm btn-primary mb-4 open">Create New Data Group</a>
						</div>
						<div class="filter cm-content-box box-primary">
							<div class="content-title SlideToolHeader">
								<div class="cpa">
									<i class="fa-solid fa-file-lines me-1"></i>All Data Groups
								</div>
								<div class="tools">
									<a href="javascript:void(0);" class="expand handle"><i class="fal fa-angle-down"></i></a>
								</div>
							</div>
							<div class="cm-content-body form excerpt">
								<div class="card-body pb-4">
									<div class="table-responsive">
										<table class="table">
											<thead>
												<tr>
													<th>ID</th>
													<th>Group Name</th>
													<th>No. of Records</th>
                                                    <th>Actions</th>
												</tr>
											</thead>
											<tbody>
                                                @foreach ($groups as $group)
                                                    <tr>
                                                        <td>{{ $group->id }}</td>
                                                        <td><a href="{{ route('admin.view.group.preview', ['id' => $group->id]) }}">{{ $group->name }}</a></td>
                                                        <td>{{ DB::table('leads')->where('group_id', $group->id)->count() }}</td>
                                                        <td class="text-nowrap">

                                                            <a href="{{ route('admin.view.group.update', ['id' => $group->id]) }}" class="btn btn-warning btn-sm content-icon">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a href="{{ route('admin.view.group.preview', ['id' => $group->id]) }}" class="btn btn-success btn-sm content-icon">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.view.group.export', ['id' => $group->id]) }}" class="btn btn-success btn-sm content-icon">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                            <a href="javascript:handleDelete({{ $group->id }});" class="btn btn-danger btn-sm content-icon">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
											</tbody>
										</table>
										<div class="d-flex align-items-center justify-content-between flex-wrap">
											<p class="mb-2 me-3">Page 1 of 5, showing 2 records out of 8 total, starting on record 1, ending on 2</p>
											<nav aria-label="Page navigation example mb-2">
											  <ul class="pagination mb-2 mb-sm-0">
												<li class="page-item"><a class="page-link" href="javascript:void(0);"><i class="fa-solid fa-angle-left"></i></a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
												<li class="page-item"><a class="page-link " href="javascript:void(0);"><i class="fa-solid fa-angle-right"></i></a></li>
											  </ul>
											</nav>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
        <!--**********************************
            Content body end
        ***********************************-->


@endsection
