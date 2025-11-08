<?php
$pageTitle = 'تعديل منتج';
require __DIR__ . '/../layouts/header.php';
?>

<h1 class="page-title">تعديل منتج: <?= escape($product['name']) ?></h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error" style="background: #fee; border: 1px solid #fcc; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #c00;">
        <strong>خطأ:</strong> <?= escape($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" style="background: #efe; border: 1px solid #cfc; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #060;">
        <strong>نجح:</strong> <?= escape($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/admin/products/update/<?= $product['id'] ?>" enctype="multipart/form-data" id="editProductForm">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h2>معلومات المنتج</h2></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">اسم المنتج <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= escape($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">التصنيف <span class="required">*</span></label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= escape($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-control" rows="4"><?= escape($product['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h2>التسعير</h2></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">سعر التكلفة</label>
                    <input type="number" name="cost_price" class="form-control" step="0.01" value="<?= $product['cost_price'] ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">السعر الأصلي <span class="required">*</span></label>
                    <input type="number" name="regular_price" class="form-control" step="0.01" value="<?= $product['regular_price'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">سعر الخصم</label>
                    <input type="number" name="discount_price" class="form-control" step="0.01" value="<?= $product['discount_price'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                    <span>منتج مميز</span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h2>الصور والفيديو</h2></div>
        <div class="card-body">
            <!-- Main Image -->
            <div class="form-group">
                <label class="form-label">الصورة الرئيسية الحالية</label>
                <div style="margin-bottom: 15px;">
                    <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" style="max-width: 300px; border-radius: 8px; border: 2px solid var(--admin-border);">
                </div>
                
                <label class="form-label">تغيير الصورة الرئيسية</label>
                <input type="file" name="main_image" class="form-control" accept="image/*" id="mainImageInput" onchange="previewMainImage(this)">
                <div id="mainImagePreview" style="margin-top: 15px;"></div>
            </div>
            
            <!-- Gallery Images -->
            <div class="form-group">
                <label class="form-label">الصور المتعددة الحالية</label>
                <?php if (!empty($product['images'])): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-bottom: 15px;" id="currentGallery">
                        <?php foreach ($product['images'] as $image): ?>
                            <div style="position: relative;" id="gallery-img-<?= $image['id'] ?>">
                                <img src="<?= UPLOAD_URL ?>/products/<?= escape($image['image_path']) ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid var(--admin-border);">
                                <button type="button" onclick="deleteGalleryImage(<?= $image['id'] ?>)" style="position: absolute; top: 5px; right: 5px; background: var(--admin-error); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 18px;">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--admin-text-muted); margin-bottom: 15px;">لا توجد صور إضافية</p>
                <?php endif; ?>
                
                <label class="form-label">إضافة صور جديدة (حتى 6 صور)</label>
                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple id="galleryImagesInput" onchange="previewGalleryImages(this)">
                <div id="galleryPreview" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;"></div>
            </div>
            
            <!-- Video -->
            <div class="form-group">
                <label class="form-label">فيديو المنتج</label>
                
                <?php if (!empty($product['videos'])): ?>
                    <?php $video = $product['videos'][0]; ?>
                    <div style="margin-bottom: 15px;" id="currentVideo">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">الفيديو الحالي:</label>
                        <?php if ($video['video_type'] === 'youtube'): ?>
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 400px; border-radius: 8px;">
                                <iframe src="https://www.youtube.com/embed/<?= getYoutubeId($video['video_path']) ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <video controls style="max-width: 400px; border-radius: 8px; border: 2px solid var(--admin-border);">
                                <source src="<?= UPLOAD_URL ?>/videos/<?= escape($video['video_path']) ?>" type="video/mp4">
                            </video>
                        <?php endif; ?>
                        <button type="button" onclick="deleteVideo(<?= $video['id'] ?>)" class="btn btn-danger btn-sm" style="margin-top: 10px;">حذف الفيديو</button>
                    </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <input type="radio" name="video_type" value="upload" checked onchange="toggleVideoInput()">
                        <span>رفع فيديو جديد</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="video_type" value="youtube" onchange="toggleVideoInput()">
                        <span>رابط يوتيوب</span>
                    </label>
                </div>
                
                <div id="uploadVideoSection">
                    <input type="file" name="product_video" class="form-control" accept="video/*" id="videoInput" onchange="previewVideo(this)">
                    <small style="color: var(--admin-text-muted); display: block; margin-top: 5px;">الحد الأقصى: 50 ميجابايت - الصيغ المدعومة: MP4, WebM</small>
                    <div id="videoPreview" style="margin-top: 15px;"></div>
                </div>
                
                <div id="youtubeVideoSection" style="display: none;">
                    <input type="text" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." id="youtubeInput">
                    <small style="color: var(--admin-text-muted); display: block; margin-top: 5px;">الصق رابط الفيديو من يوتيوب</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>الألوان والمقاسات</h2>
        </div>
        <div class="card-body">
            <!-- Current Colors with Sizes -->
            <div style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px;">الألوان والمقاسات الحالية:</h3>
                <?php foreach ($product['colors'] as $color): ?>
                    <div style="background: var(--admin-card); border: 2px solid var(--admin-border); border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span style="display: inline-block; width: 30px; height: 30px; background: <?= escape($color['color_hex']) ?>; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                            <strong style="font-size: 16px;"><?= escape($color['color_name']) ?></strong>
                            <span style="color: var(--admin-text-muted); font-size: 14px;">(ID: <?= $color['id'] ?>)</span>
                        </div>
                        
                        <?php if (!empty($color['sizes'])): ?>
                            <div style="margin-top: 10px;">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block; color: var(--admin-text-muted);">المقاسات:</label>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
                                    <?php foreach ($color['sizes'] as $size): ?>
                                        <div style="background: var(--admin-bg); padding: 10px; border-radius: 6px; border: 1px solid var(--admin-border);">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-weight: 600; font-size: 15px;"><?= escape($size['size_name']) ?></span>
                                                <span style="background: var(--admin-primary); color: #000; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                                    <?= $size['stock_quantity'] ?> قطعة
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--admin-text-muted); font-size: 14px; margin-top: 10px;">لا توجد مقاسات لهذا اللون</p>
                        <?php endif; ?>
                        
                        <!-- Edit Size Button -->
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--admin-border);">
                            <a href="<?= BASE_URL ?>/admin/products/edit-sizes/<?= $color['id'] ?>?product_id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: middle; margin-left: 4px;">
                                    <path d="M10 1L13 4L5 12H2V9L10 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                تعديل المقاسات
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Add New Colors -->
            <div style="border-top: 2px solid var(--admin-border); padding-top: 20px;">
                <h3 style="margin-bottom: 15px;">إضافة ألوان جديدة:</h3>
                <div id="colorsContainer">
                    <!-- Colors will be added here dynamically -->
                </div>
                
                <button type="button" class="btn btn-sm btn-outline" onclick="addColorField()">إضافة لون جديد</button>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 15px;">
        <button type="submit" class="btn btn-primary" id="saveBtn">حفظ التعديلات</button>
        <a href="<?= BASE_URL ?>/admin/products" class="btn btn-outline">إلغاء</a>
    </div>
</form>

<!-- Debug Info -->
<div id="debugInfo" style="display: none; margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 8px;">
    <h4>Debug Information:</h4>
    <pre id="debugContent"></pre>
</div>

<script>
let colorIndex = 0;
let sizeIndexes = {};

// Add new color field
function addColorField() {
    const container = document.getElementById('colorsContainer');
    const field = document.createElement('div');
    field.className = 'color-field';
    field.style.cssText = 'border: 2px solid var(--admin-border); padding: 15px; border-radius: 8px; margin-bottom: 15px;';
    field.innerHTML = `
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 10px;">
            <input type="text" name="new_colors[${colorIndex}][name]" placeholder="اسم اللون" class="form-control">
            <input type="color" name="new_colors[${colorIndex}][hex]" class="form-control">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">حذف اللون</button>
        </div>
        <div style="margin-top: 10px;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">المقاسات:</label>
            <div class="sizes-container" data-color-index="${colorIndex}">
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px;">
                    <input type="text" name="new_colors[${colorIndex}][sizes][0][name]" placeholder="المقاس (مثال: S, M, L)" class="form-control">
                    <input type="number" name="new_colors[${colorIndex}][sizes][0][stock]" placeholder="الكمية" class="form-control" min="0">
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

// Add size to new color
function addSizeField(colorIdx) {
    const container = document.querySelector(`.sizes-container[data-color-index="${colorIdx}"]`);
    if (!sizeIndexes[colorIdx]) sizeIndexes[colorIdx] = 1;
    
    const sizeField = document.createElement('div');
    sizeField.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px;';
    sizeField.innerHTML = `
        <input type="text" name="new_colors[${colorIdx}][sizes][${sizeIndexes[colorIdx]}][name]" placeholder="المقاس" class="form-control">
        <input type="number" name="new_colors[${colorIdx}][sizes][${sizeIndexes[colorIdx]}][stock]" placeholder="الكمية" class="form-control" min="0">
        <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()">حذف</button>
    `;
    container.appendChild(sizeField);
    sizeIndexes[colorIdx]++;
}

// Preview Functions
function previewMainImage(input) {
    const preview = document.getElementById('mainImagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="position: relative; display: inline-block;">
                    <img src="${e.target.result}" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border);">
                    <button type="button" onclick="clearMainImage()" style="position: absolute; top: 10px; right: 10px; background: var(--admin-error); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">×</button>
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
                    <img src="${e.target.result}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid var(--admin-border);">
                    <button type="button" onclick="removeGalleryImage(${index})" style="position: absolute; top: 5px; right: 5px; background: var(--admin-error); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 18px;">×</button>
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

function deleteGalleryImage(imageId) {
    if (!confirm('هل أنت متأكد من حذف هذه الصورة؟')) return;
    
    fetch(`<?= BASE_URL ?>/admin/products/delete-image/${imageId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            csrf_token: '<?= generateCsrfToken() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`gallery-img-${imageId}`).remove();
            alert('تم حذف الصورة بنجاح');
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    })
    .catch(error => {
        alert('حدث خطأ في الاتصال');
        console.error('Error:', error);
    });
}

function previewVideo(input) {
    const preview = document.getElementById('videoPreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        preview.innerHTML = `
            <div style="position: relative; display: inline-block;">
                <video controls style="max-width: 400px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border);">
                    <source src="${url}" type="${file.type}">
                </video>
                <button type="button" onclick="clearVideo()" style="position: absolute; top: 10px; right: 10px; background: var(--admin-error); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">×</button>
            </div>
        `;
    }
}

function clearVideo() {
    document.getElementById('videoInput').value = '';
    document.getElementById('videoPreview').innerHTML = '';
}

function deleteVideo(videoId) {
    if (!confirm('هل أنت متأكد من حذف هذا الفيديو؟')) return;
    
    fetch(`<?= BASE_URL ?>/admin/products/delete-video/${videoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            csrf_token: '<?= generateCsrfToken() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('currentVideo').remove();
            alert('تم حذف الفيديو بنجاح');
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    })
    .catch(error => {
        alert('حدث خطأ في الاتصال');
        console.error('Error:', error);
    });
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

// Form submission handler
const form = document.getElementById('editProductForm');
if (form) {
    form.addEventListener('submit', function(e) {
        console.log('=== Form Submission Started ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        
        // Check for empty new_colors fields
        const colorFields = document.querySelectorAll('.color-field');
        console.log('Found color fields:', colorFields.length);
        
        let hasEmptyRequired = false;
        let removedCount = 0;
        
        colorFields.forEach((colorField, index) => {
            const nameInput = colorField.querySelector('input[name*="[name]"]');
            const hexInput = colorField.querySelector('input[name*="[hex]"]');
            
            if (nameInput && hexInput) {
                console.log(`Color field ${index}:`, {
                    name: nameInput.value,
                    hex: hexInput.value
                });
                
                // If one is filled but not the other
                if ((nameInput.value && !hexInput.value) || (!nameInput.value && hexInput.value)) {
                    hasEmptyRequired = true;
                    console.log(`Color field ${index} has incomplete data`);
                }
                
                // If both are empty, remove the field
                if (!nameInput.value && !hexInput.value) {
                    console.log(`Removing empty color field ${index}`);
                    colorField.remove();
                    removedCount++;
                }
            }
        });
        
        console.log('Removed empty fields:', removedCount);
        
        if (hasEmptyRequired) {
            e.preventDefault();
            console.log('=== Form Submission BLOCKED - Incomplete Data ===');
            alert('من فضلك أكمل بيانات اللون (الاسم واللون) أو احذف الحقل');
            return false;
        }
        
        console.log('=== Form Validation Passed ===');
        console.log('Form will submit now...');
        
        // Show loading indicator
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin-left: 8px;"></span> جاري الحفظ...';
        }
    });
}

// Debug button
const saveBtn = document.getElementById('saveBtn');
if (saveBtn) {
    saveBtn.addEventListener('click', function(e) {
        console.log('=== Save Button Clicked ===');
        console.log('Button type:', this.type);
    });
}

// Add spinner animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
