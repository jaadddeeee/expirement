{{-- add modal --}}
<div class="modal fade" id="modalCoach" tabindex="-1" aria-labelledby="coachModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Coach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmAdd" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center p-1" id="msg"></div>
                    @if (auth()->user()->AllowSuper == 1)
                        <div class="row mt-1  ">
                            <div class = "form-group">
                                <div class="col-auto">
                                    <label class="form-label">Campus </label><span class="text-danger ps-1">*</span>
                                    <select name="Campus" id="Campus" class="form-select">
                                        <option value="0">Select Campus</option>
                                        @foreach (GENERAL::Campuses() as $index => $campus)
                                            <option value="{{ $index }}"
                                                <?= $index == $campus ? 'Selected' : '' ?>>{{ $campus['Campus'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row all mt-2">
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto position-relative">
                                    <label class="form-label">Employee </label><span class="text-danger ps-1">*</span>
                                    <!-- Employee Input Field -->
                                    <input type="text" name="Emp" id="Emp" class="form-control"
                                        placeholder="Enter employee name">
                                        <input type="hidden" name="EmployeeID" id="EmployeeID">
                                    <div id="EmployeeDropdown" class="dropdown-menu position-absolute w-100 shadow bg-white"
                                        style="display: none; z-index: 1050; max-height: 170px; overflow-y: auto; border: 1px; border-radius: 5px;">
                                        <ul id="EmployeeList" class="list-group list-group-flush"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Coach Type </label><span class="text-danger ps-1">*</span>
                                    <select name="coachType" id="coachType" class="form-select">
                                        <option value="0">Select Type</option>
                                        @foreach (GENERAL::CoachType() as $index => $type)
                                            <option value = "{{ $index }}">{{ $type['Type'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Event </label><span class="text-danger ps-1">*</span>
                                    <select name="Event" id="Event" class="form-select">
                                        <option value="0">Select Event</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row all mt-2">
                                <div class="form-group">
                                    <div class="col-auto">
                                        <label class="form-label">Coach Picture: </label>
                                        <input type="file" name="CoachImage" id="image" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- update modal --}}
<div class="modal fade" id="updateModalCoach" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true"
    style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4 text-warning" id="eventModalLabel">Update Coach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmUpdate">
                    @csrf
                    <div class="text-center p-1" id="updatemsg"></div>
                    <input hidden type = "text" name = "hiddentID" id="hiddentID" value="">
                    @if (auth()->user()->AllowSuper == 1)
                        <div class="row mt-1">
                            <div class = "form-group">
                                <div class="col-auto">
                                    <label class="form-label">Campus</label>
                                    <select name="updateCampus" id="updateCampus" class="form-control" disabled>
                                        <option value="0"></option>
                                        @foreach (GENERAL::Campuses() as $index => $campus)
                                            <option value="{{ $index }}">{{ $campus['Campus'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label class="form-label">Employee</label>
                                <!-- Employee Input Field -->
                                <input type="text" name="updateEmp" id="updateEmp" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Coach Type</label>
                                    <select name="updateCT" id="updateCT" class="form-select">
                                        <option value="0"></option>
                                        @foreach (GENERAL::CoachType() as $index => $type)
                                            <option value = "{{ $index }}">{{ $type['Type'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Event</label>
                                    <select name="updateEvent" id="updateEvent" class="form-select">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="btn-update">Update Coach</button>
            </div>
        </div>
    </div>
</div>
