<form id="surveyForm">
    <input type="text" name="title" placeholder="Survey Title" required>
    <div id="questionsContainer"></div>
    <button type="button" id="addQuestion">Add Question</button>
    <button type="submit">Create Survey</button>
</form>

<script>
    let questionCount = 0;

    document.getElementById('addQuestion').addEventListener('click', function () {
        const questionTemplate = `
            <div class="question">
                <input type="text" name="questions[${questionCount}][question]" placeholder="Question" required>
                <select name="questions[${questionCount}][type]">
                    <option value="text">Text</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                </select>
                <div class="answers"></div>
                <button type="button" class="addAnswer">Add Answer</button>
            </div>
        `;
        document.getElementById('questionsContainer').insertAdjacentHTML('beforeend', questionTemplate);
        questionCount++;
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.className === 'addAnswer') {
            const answerTemplate = `<input type="text" name="questions[${questionCount - 1}][answers][]" placeholder="Answer">`;
            e.target.previousElementSibling.insertAdjacentHTML('beforeend', answerTemplate);
        }
    });

    document.getElementById('surveyForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route("surveys.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Survey created successfully!');
                window.location.reload();
            }
        });
    });
</script>
