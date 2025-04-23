
{{-- add modal --}}
<div class="modal fade" id="modalEvent" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmAdd">
                @csrf
                <div id="msg"></div>
                    <div class = "form-group">
                    <input type="text" name = "event" id="event" class = "mb-4 form-control" placeholder = "Event">
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
<div class="modal fade" id="updateModalEvent" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4 text-warning" id="eventModalLabel">Update Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <div class="modal-body">
            <form id="frmUpdate">
                @csrf
                <div id="updatemsg"></div>
                <input hidden type = "text" name = "hiddentID" id="hiddentID" value="">
                    <div class = "form-group">
                        <input type="text" name = "updateEvent" id="updateEvent" class = "mb-4 form-control" placeholder = "Event">
                    </div>
            </form>
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="btn-update">Update</button>
            </div>
        </div>
    </div>
</div>