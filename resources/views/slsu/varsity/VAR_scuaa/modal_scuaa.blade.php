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
                <form id="frmSetScuaa" enctype="multipart/form-data">
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
                    <div class="row mt-1">
                        <div class = "form-group">
                            <div class="col-auto">
                                <label class="form-label">Theme:</label>
                                <input type="text" name="Theme" id="Theme" class="form-control">
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
                                    <input class="form-control" type="file" id="Logo" name="ScuaaLogo" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <div class="col-auto">
                                    <label class="form-label">Date:</label>
                                    <input type="text" name="Date" id="date-range"
                                        placeholder="YYYY-MM-DD to YYYY-MM-DD"
                                        class="form-control flatpickr-input text-decoration-none">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btn-saveScuaa">Save</button>
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
                <button type="button" class="btn btn-primary" id="btn-saveEvent">Save</button>
            </div>
        </div>
    </div>
</div>
