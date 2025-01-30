

<!-- Add/Edit Modal -->

<div class="modal fade" id="modalDeptEmployee" tabindex="-1" aria-labelledby="clientModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="clientModalLabel">Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmEmployee">
                    @csrf
                    <input hidden type = "text" value = "" name = "hiddentID" id = "hiddentID">
                    <div class="row">
                      <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "FirstName">First Name:</label>
                              <input  type="text" class = "form-control" name="FirstName" id="FirstName" autofocus>
                          </div>
                      </div>

                      <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "MiddleName">Middle Name:</label>
                              <input  type="text" class = "form-control" name="MiddleName" id="MiddleName">
                          </div>
                      </div>

                      <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "LastName">Last Name:</label>
                              <input  type="text" class = "form-control" name="LastName" id="LastName">
                          </div>
                      </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "EmploymentStatus">Employment Status:</label>
                              <select class = "form-control">
                                <option></option>
                                <option value = "Part Timer">COS Faculty</option>
                              </select>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "CurrentItem">Academic Rank:</label>
                              <input  type="text" class = "form-control" name="CurrentItem" id="CurrentItem">
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class = "form-group">
                              <label class = "text-dark" for = "SalaryGrade">Rate / Salary Grade:</label>
                              <input  type="text" class = "form-control" name="SalaryGrade" id="SalaryGrade">
                          </div>
                        </div>


                    </div>

                    <div class = "form-group mt-2">
                        <button type="submit" class = "btn btn-success" id="savesharecapital">Save</button>
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


