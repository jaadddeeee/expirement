<div class="modal fade" id="modalSurvey" data-bs-backdrop="static" tabindex="-1" style="display: none;" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content"  id = "frmSurvey">
      <div class="modal-header">
        <h4 class="modal-title" id="backDropModalTitle">New Survey</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          @csrf
          <input type = "text" value = "" id = "hiddenID" name = "hiddenID" hidden>
          <div id="SurveyMsg"></div>
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="title" name = "title" class="form-control" placeholder="Survey Title">
                <label for="title">Name</label>
              </div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-2">
              <div class="form-floating form-floating-outline">
                <input type="date" id="date_start" name = "date_start" class="form-control" placeholder="Start">
                <label for="date_start">Survey Starts</label>
              </div>
            </div>
            <div class="col-6 mb-2">
              <div class="form-floating form-floating-outline">
                <input type="date" id="date_end" name = "date_end" class="form-control">
                <label for="date_end">Survey Ends</label>
              </div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col mb-2">
              <div class="form-floating form-floating-outline">
                <textarea id="description" rows = "5" name = "description" class="form-control" placeholder = "Enter Description"></textarea>
                <label for="description">Description</label>
              </div>
            </div>
          </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary waves-effect" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
      </div>
    </form>
  </div>
</div>
