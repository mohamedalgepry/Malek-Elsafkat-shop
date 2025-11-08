<?php
$pageTitle = 'إضافة منتج';
require __DIR__ . '/../layouts/header.php';
?>

<h1 class="page-title">إضافة منتج جديد</h1>

<form method="POST" action="<?= BASE_URL ?>/admin/products/store" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h2>معلومات المنتج</h2></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">اسم المنتج <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">التصنيف <span class="required">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">اختر التصنيف</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= escape($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h2>التسعير</h2></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">سعر التكلفة</label>
                    <input type="number" name="cost_price" class="form-control" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">السعر الأصلي <span class="required">*</span></label>
                    <input type="number" name="regular_price" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">سعر الخصم</label>
                    <input type="number" name="discount_price" class="form-control" step="0.01" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_featured" value="1">
                    <span>منتج مميز</span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h2>الصور والفيديو</h2></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">الصورة الرئيسية <span class="required">*</span></label>
                <input type="file" name="main_image" class="form-control" accept="image/*" required id="mainImageInput" onchange="previewMainImage(this)">
                <div id="mainImagePreview" style="margin-top: 15px;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">صور إضافية (حتى 6 صور)</label>
                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple id="galleryImagesInput" onchange="previewGalleryImages(this)">
                <div id="galleryPreview" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">فيديو المنتج</label>
                <div style="margin-bottom: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <input type="radio" name="video_type" value="upload" checked onchange="toggleVideoInput()">
                        <span>رفع فيديو</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="video_type" value="youtube" onchange="toggleVideoInput()">
                        <span>رابط يوتيوب</span>
                    </label>
                </div>
                
                <div id="uploadVideoSection">
                    <input type="file" name="product_video" class="form-control" accept="video/*" id="videoInput" onchange="previewVideo(this)">
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">الحد الأقصى: 50 ميجابايت - الصيغ المدعومة: MP4, WebM</small>
                    <div id="videoPreview" style="margin-top: 15px;"></div>
                </div>
                
                <div id="youtubeVideoSection" style="display: none;">
                    <input type="text" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." id="youtubeInput">
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">الصق رابط الفيديو من يوتيوب</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>الألوان والمقاسات</h2>
            <button type="button" class="btn btn-sm btn-outline" onclick="addColorField()">إضافة لون</button>
        </div>
        <div class="card-body">
            <div id="colorsContainer">
                <div class="color-field" style="border: 2px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="colors[0][name]" placeholder="اسم اللون" class="form-control" required>
                        <input type="color" name="colors[0][hex]" class="form-control" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">حذف اللون</button>
                    </div>
                    <div style="margin-top: 10px;">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">المقاسات:</label>
                        <div class="sizes-container" data-color-index="0">
                            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px;">
                                <input type="text" name="colors[0][sizes][0][name]" placeholder="المقاس (مثال: S, M, L)" class="form-control" required>
                                <input type="number" name="colors[0][sizes][0][stock]" placeholder="الكمية" class="form-control" min="0" required>
                                <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addSizeField(0)" style="margin-top: 5px;">إضافة مقاس</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 15px;">
        <button type="submit" class="btn btn-primary">حفظ المنتج</button>
        <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline">إلغاء</a>
    </div>
</form>

<script>
// Preview Functions
function previewMainImage(input) {
    const preview = document.getElementById('mainImagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="position: relative; display: inline-block;">
                    <img src="${e.target.result}" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--border);">
                    <button type="button" onclick="clearMainImage()" style="position: absolute; top: 10px; right: 10px; background: var(--error); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">×</button>
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearMainImage() {
    document.getElementById('mainImageInput').value = '';
    document.getElementById('mainImagePreview').innerHTML = '';
}

function previewGalleryImages(input) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'position: relative;';
                div.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid var(--border);">
                    <button type="button" onclick="removeGalleryImage(${index})" style="position: absolute; top: 5px; right: 5px; background: var(--error); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 18px;">×</button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function removeGalleryImage(index) {
    const input = document.getElementById('galleryImagesInput');
    const dt = new DataTransfer();
    const files = input.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) dt.items.add(files[i]);
    }
    
    input.files = dt.files;
    previewGalleryImages(input);
}

function previewVideo(input) {
    const preview = document.getElementById('videoPreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        preview.innerHTML = `
            <div style="position: relative; display: inline-block;">
                <video controls style="max-width: 400px; max-height: 300px; border-radius: 8px; border: 2px solid var(--border);">
                    <source src="${url}" type="${file.type}">
                </video>
                <button type="button" onclick="clearVideo()" style="position: absolute; top: 10px; right: 10px; background: var(--error); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">×</button>
            </div>
        `;
    }
}

function clearVideo() {
    document.getElementById('videoInput').value = '';
    document.getElementById('videoPreview').innerHTML = '';
}

function toggleVideoInput() {
    const uploadSection = document.getElementById('uploadVideoSection');
    const youtubeSection = document.getElementById('youtubeVideoSection');
    const videoType = document.querySelector('input[name="video_type"]:checked').value;
    
    if (videoType === 'upload') {
        uploadSection.style.display = 'block';
        youtubeSection.style.display = 'none';
        document.getElementById('youtubeInput').value = '';
    } else {
        uploadSection.style.display = 'none';
        youtubeSection.style.display = 'block';
        document.getElementById('videoInput').value = '';
        document.getElementById('videoPreview').innerHTML = '';
    }
}

// Colors and Sizes
let colorIndex = 1;
let sizeIndexes = {0: 1}; // Track size indexes for each color

function addColorField() {
    const container = document.getElementById('colorsContainer');
    const field = document.createElement('div');
    field.className = 'color-field';
    field.style.cssText = 'border: 2px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 15px;';
    field.innerHTML = `
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 10px;">
            <input type="text" name="colors[${colorIndex}][name]" placeholder="اسم اللون" class="form-control" required>
            <input type="color" name="colors[${colorIndex}][hex]" class="form-control" required>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">حذف اللون</button>
        </div>
        <div style="margin-top: 10px;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">المقاسات:</label>
            <div class="sizes-container" data-color-index="${colorIndex}">
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px;">
                    <input type="text" name="colors[${colorIndex}][sizes][0][name]" placeholder="المقاس (مثال: S, M, L)" class="form-control" required>
                    <input type="number" name="colors[${colorIndex}][sizes][0][stock]" placeholder="الكمية" class="form-control" min="0" required>
                    <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()">حذف</button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addSizeField(${colorIndex})" style="margin-top: 5px;">إضافة مقاس</button>
        </div>
    `;
    container.appendChild(field);
    sizeIndexes[colorIndex] = 1;
    colorIndex++;
}

function addSizeField(colorIdx) {
    const container = document.querySelector(`.sizes-container[data-color-index="${colorIdx}"]`);
    if (!sizeIndexes[colorIdx]) sizeIndexes[colorIdx] = 1;
    
    const sizeField = document.createElement('div');
    sizeField.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px;';
    sizeField.innerHTML = `
        <input type="text" name="colors[${colorIdx}][sizes][${sizeIndexes[colorIdx]}][name]" placeholder="المقاس" class="form-control" required>
        <input type="number" name="colors[${colorIdx}][sizes][${sizeIndexes[colorIdx]}][stock]" placeholder="الكمية" class="form-control" min="0" required>
        <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()">حذف</button>
    `;
    container.appendChild(sizeField);
    sizeIndexes[colorIdx]++;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
