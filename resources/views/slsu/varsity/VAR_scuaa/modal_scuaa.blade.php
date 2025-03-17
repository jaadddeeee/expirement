{{-- add modal --}}
{{-- <div class="modal fade" id="modalVarsity" tabindex="-1" aria-labelledby="coachModal" aria-hidden="true"
    style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Varsity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmAdd">
                    @csrf
                    <div class="text-center p-1" id="msg"></div>
                    @if (auth()->user()->AllowSuper == 1)
                        <div class="row mt-1  ">
                            <div class = "form-group">
                                <div class="col-auto">
                                    <label class="form-label">Campus <span class = 'text-danger'>*</span></label>
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
                        <div class="form-group">
                            <div class="col-auto">
                                <label class="form-label">Student</label>
                                <input type="text" name="Stud" id="Stud" class="form-select"
                                    placeholder="Enter student name">
                                <div id="StudentDropdown" class="dropdown-menu w-80 shadow bg-white"
                                    style="display: none; position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto;">
                                    <ul id="StudentList" class="list-group list-group-flush"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label class="form-label">Event</label>
                                <select name="Event" id="Event" class="form-select">
                                    <option value="0">Select Event</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label for="Description" class="form-label">School Year:</label>
                                <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                                    <option value="0"></option>
                                    @foreach (GENERAL::SchoolYears() as $index => $sy)
                                        <option value="{{ $sy }}">{{ $sy }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label for="Description" class="form-label">Semester:</label>
                                <select class = "form-select" name = "Semester" id = "Semester">
                                    <option value="0"></option>
                                    @foreach (GENERAL::Semesters() as $index => $sem)
                                        <option value="{{ $index }}">{{ $sem['Long'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div> --}}

{{-- add scuaa --}}
<div class="modal fade border" id="scuaaModal" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true"
    style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">SCUAA Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmUpdate">
                    @csrf
                    <div class="text-center p-1" id="updatemsg"></div>
                    <input hidden type = "text" name = "hiddentID" id="hiddentID" value="">
                    <div class="row mt-1">
                        <div class = "form-group">
                            <div class="col-auto">
                                <label class="form-label">Title:</label>
                                <input type="text" name="title" id="title" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label class="form-label">University:</label>
                                <input type="text" name="University" id="University" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Municipality:</label>
                                    <input type="text" name="Municipality" id="municipality" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Province:</label>
                                    <input type="text" name="Province" id="Province" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">SCUAA Logo:</label>
                                    <input class="form-control" type="file" id="formValidationFile"
                                        name="formValidationFile">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Date:</label>
                                    <input type="text" name="date" id="date-range"
                                        placeholder="YYYY-MM-DD to YYYY-MM-DD" class="form-control flatpickr-input">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btn-update">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- add Event --}}
<div class="modal fade" id="modalList" tabindex="-1" aria-labelledby="listModal" aria-hidden="true"
    style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">SCUAA Event Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmSet">
                    @csrf
                    <div class="text-center p-1" id="msg"></div>
                    <div class="row all mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label class="form-label">Event:</label>
                                <select name="filterEvent" id="filterEvent" class="form-select">
                                    <option value="0">Select Event</option>
                                    @foreach ($Events as $Event)
                                        <option value="{{ $Event->id }}"
                                            {{ request('filterEvent') == $Event ? 'selected' : '' }}>{{ $Event->event }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row all mt-2">
                        <div class="form-group">
                            <div class="col-auto">
                                <label for="Description" class="form-label">Total Participant:</label>
                                <input type="number" name="totalPart" id="totalPart" class="form-control"
                                    placeholder="Enter number of participants">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>
