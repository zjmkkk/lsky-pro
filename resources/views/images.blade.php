@section('title', '我的图片')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/justified-gallery/justifiedGallery.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/viewer-js/viewer.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/context-js/context-js.css') }}">
@endpush

<x-app-layout>
    <div class="relative flex justify-between items-center px-2 py-2 z-[3] top-0 left-0 right-0 bg-white border-solid border-b">
        <div class="space-x-2 flex justify-between items-center">
            <a class="text-sm py-2 px-3 hover:bg-gray-100 rounded text-gray-800" href="javascript:getAlbums()"><i class="fas fa-bars text-blue-500"></i> 相册</a>
            <div class="block md:hidden">
                <x-dropdown direction="right">
                    <x-slot name="trigger">
                        <a class="text-sm py-2 px-3 hover:bg-gray-100 rounded text-gray-800" href="javascript:void(0)"><i class="fas fa-ellipsis-h text-blue-500"></i></a>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('/') }}">移动到相册</x-dropdown-link>
                        <x-dropdown-link href="{{ route('/') }}">标记为不健康</x-dropdown-link>
                        <x-dropdown-link href="{{ route('/') }}" class="text-red-500">公开</x-dropdown-link>
                        <x-dropdown-link href="{{ route('/') }}" class="text-red-500">删除</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
        <div class="flex space-x-2 items-center">
            <input type="text" id="search" class="px-2.5 py-1.5 border-0 outline-none rounded bg-gray-100 text-sm transition-all duration-300 hidden md:block md:w-36 md:hover:w-52 md:focus:w-52" placeholder="输入关键字搜索...">
            <x-dropdown direction="left">
                <x-slot name="trigger">
                    <a id="order" class="text-sm py-2 px-3 hover:bg-gray-100 rounded text-gray-800" href="javascript:void(0)">
                        <span>最新</span>
                        <i class="fas fa-sort-alpha-up text-blue-500"></i>
                    </a>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link href="javascript:void(0)" @click="setOrderBy('newest1'); open = false">最新
                    </x-dropdown-link>
                    <x-dropdown-link href="javascript:void(0)" @click="setOrderBy('earliest'); open = false">最早
                    </x-dropdown-link>
                    <x-dropdown-link href="javascript:void(0)" @click="setOrderBy('utmost'); open = false">最大
                    </x-dropdown-link>
                    <x-dropdown-link href="javascript:void(0)" @click="setOrderBy('least'); open = false">最小
                    </x-dropdown-link>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
    <div class="relative inset-0 h-full overflow-hidden">
        <!-- content -->
        <div id="photos-scroll" class="absolute inset-0 overflow-y-scroll select-none">
            <div id="photos-grid"></div>
        </div>
        <!-- right drawer -->
        <div id="drawer-mask" class="absolute hidden inset-0 bg-gray-500 bg-opacity-50 z-[2]" onclick="drawer.close()"></div>
        <div id="drawer" class="absolute bg-white w-52 md:w-72 top-0 -right-[1000px] bottom-0 z-[2] flex flex-col transition-all duration-300">
            <div class="flex justify-between items-center text-md px-3 py-1 border-b">
                <span class="text-gray-600 truncate" id="drawer-title"></span>
                <a href="javascript:drawer.close()" class="p-2"><i class="fas fa-times text-blue-500"></i></a>
            </div>
            <div id="drawer-content" class="overflow-y-auto"></div>
        </div>
    </div>

    <script type="text/html" id="photos-item-tpl">
        <a href="javascript:void(0)" class="photos-item relative cursor-default rounded outline outline-2 outline-offset-2 outline-transparent">
            <div class="photo-selector absolute z-[2] top-0 right-0 overflow-hidden cursor-pointer sm:hidden group-hover:block">
                <div class="p-1 text-xl sm:text-2xl">
                    <i class="fas fa-check-circle block rounded-full bg-white text-white border border-gray-500"></i>
                </div>
            </div>
            <div class="photo-mask absolute left-0 right-0 bottom-0 h-20 z-[1] bg-gradient-to-t from-black" onclick="$(this).siblings('img').trigger('click')">
                <div class="absolute left-2 bottom-2 text-white z-[2] w-[90%]">
                    <p class="text-sm truncate" title="__name__">__name__</p>
                    <p class="text-xs" title="__human_date__">__date__</p>
                </div>
            </div>
            <img alt="__name__" src="__url__" width="__width__" height="__height__">
        </a>
    </script>

    <script type="text/html" id="albums-container-tpl">
        <div id="albums-container" class="flex flex-col justify-center items-center w-full p-3 space-y-2">
            <div id="album-add" class="flex flex-col w-full hidden border rounded p-2">
                <p class="error-message text-white p-2 mb-2 text-sm bg-red-500 rounded hidden"></p>
                <form class="w-full space-y-2" action="/user/albums">
                    <input type="text" class="w-full rounded px-2.5 py-1.5 text-sm border-0 bg-gray-200" name="name" placeholder="请输入名称">
                    <textarea class="w-full resize-y rounded-md text-sm border-0 bg-gray-200" name="intro" placeholder="请输入简介"></textarea>
                </form>
                <a href="javascript:void(0)" class="w-full py-1 px-2 bg-indigo-500 text-white text-sm text-center tracking-wider font-semibold rounded-md">创建相册</a>
            </div>
        </div>
    </script>

    <script type="text/html" id="albums-item-tpl">
        <a href="javascript:void(0)" data-id="__id__" title="__intro__" class="albums-item flex justify-between items-center group px-2 h-7 rounded w-full bg-gray-100 text-gray-800 hover:bg-blue-300 hover:text-white">
            <span class="text-sm truncate w-[80%] name">__name__</span>
            <div class="flex items-center justify-center space-x-1 hidden group-hover:block">
                <span class="update"><i class="fas fa-edit text-xs"></i></span>
                <span class="delete"><i class="fas fa-trash-alt text-xs text-red-400"></i></span>
            </div>
            <span class="group-hover:hidden text-xs">__image_num__</span>
        </a>
    </script>

    <script type="text/html" id="album-update-tpl">
        <div id="album-edit" data-id="__id__" class="flex flex-col w-full border rounded p-2">
            <p class="error-message text-white p-2 mb-2 text-sm bg-red-500 rounded hidden"></p>
            <form class="w-full space-y-2" action="/user/albums/__id__">
                <input type="text" class="w-full rounded px-2.5 py-1.5 text-sm border-0 bg-gray-200" placeholder="请输入名称" name="name" value="__name__">
                <textarea class="w-full resize-y rounded-md text-sm border-0 bg-gray-200" name="intro" placeholder="请输入简介">__intro__</textarea>
            </form>
            <a href="javascript:void(0)" class="w-full py-1 px-2 bg-indigo-500 text-white text-sm text-center tracking-wider font-semibold rounded-md">确认修改</a>
        </div>
    </script>

    @push('scripts')
        <script src="{{ asset('js/justified-gallery/jquery.justifiedGallery.min.js') }}"></script>
        <script src="{{ asset('js/viewer-js/viewer.min.js') }}"></script>
        <script src="{{ asset('js/dragselect/ds.min.js') }}"></script>
        <script src="{{ asset('js/context-js/context-js.js') }}"></script>
        <script>
            let gridConfigs = {
                rowHeight: 180,
                margins: 16,
                captions: false,
                border: 10,
                waitThumbnailsLoad: false,
            };

            let selectedAlbum = 0; // 选择的相册

            const $photos = $("#photos-grid");
            const $drawer = $("#drawer");
            const $drawerMask = $('#drawer-mask');
            const viewer = new Viewer(document.getElementById('photos-grid'), {});
            const drawer = {
                open(title, $content, callback) {
                    $drawerMask.fadeIn();
                    $drawer.css('right', 0);
                    $drawer.find('#drawer-title').html(title);
                    $drawer.find('#drawer-content').html($content);
                    callback && callback();
                },
                close(callback) {
                    $drawerMask.fadeOut();
                    $drawer.css('right', '-1000px');
                    callback && callback();
                },
                toggle(title, $content, callback) {
                    if ($drawerMask.is(':hidden')) {
                        this.open(title, $content, callback);
                    } else {
                        this.close(callback);
                    }
                }
            }

            $photos.justifiedGallery(gridConfigs);

            const imagesInfinite = utils.infiniteScroll('#photos-scroll', {
                url: '{{ route('user.images') }}',
                success: function (response) {
                    if (!response.status) {
                        return toastr.error(response.message);
                    }

                    let images = response.data.images.data;
                    if (images.length <= 0 || response.data.images.current_page === response.data.images.last_page) {
                        this.finished = true;
                    }

                    let html = '';
                    for (const i in images) {
                        html += $('#photos-item-tpl').html()
                            .replace(/__name__/g, images[i].filename)
                            .replace(/__human_date__/g, images[i].human_date)
                            .replace(/__date__/g, images[i].date)
                            .replace(/__url__/g, images[i].url)
                            .replace(/__width__/g, images[i].width)
                            .replace(/__height__/g, images[i].height)
                    }

                    $photos.append(html);
                    if (ds) {
                        ds.setSelectables($photos.find('.photos-item'));
                    }
                },
                complete: function () {
                    if ($photos.html() !== '') {
                        // 由于 justifiedGallery 创建后占高度(无论是否有内容或内容被清空)，导致加载过程中在没有数据的情况下高度被拉开
                        // 所以需要在重置前销毁，重置数据后重新构建 justifiedGallery
                        if ($photos.hasClass('reset')) {
                            $photos.justifiedGallery(gridConfigs).removeClass('reset');
                        }

                        $photos.justifiedGallery('norewind')
                        viewer.update();
                    } else {
                        // 没有任何数据时销毁 justifiedGallery
                        $photos.justifiedGallery('destroy')
                    }
                }
            });

            const resetImages = (params) => {
                $photos.addClass('reset').html('').justifiedGallery('destroy');
                imagesInfinite.refresh(params);
            }

            const getAlbums = () => {
                let title = '我的相册 <i class="cursor-pointer fas fa-plus text-blue-500" onclick="$(\'#album-add\').toggleClass(\'hidden\')"></i>';
                let content = $('#albums-container-tpl').html();
                drawer.toggle(title, content, function () {
                    let $albums = $('#albums-container');
                    const CREATE_ID = '#album-add';
                    const UPDATE_ID = '#album-edit';
                    const albumsInfinite = utils.infiniteScroll('#drawer-content', {
                        url: '{{ route('user.albums') }}',
                        success: function (response) {
                            if (!response.status) {
                                return toastr.error(response.message);
                            }

                            let albums = response.data.albums.data;
                            if (albums.length <= 0 || response.data.albums.current_page === response.data.albums.last_page) {
                                this.finished = true;
                            }

                            let html = '';
                            for (const i in albums) {
                                let item = $('#albums-item-tpl').html()
                                    .replace(/__id__/g, albums[i].id)
                                    .replace(/__name__/g, albums[i].name)
                                    .replace(/__intro__/g, albums[i].intro)
                                    .replace(/__image_num__/g, albums[i].image_num)
                                if (albums[i].id === selectedAlbum) {
                                    // 选中的相册高亮
                                    item = item
                                        .replace(/bg-gray-100/g, 'bg-blue-400')
                                        .replace(/text-gray-800/g, 'text-white')
                                }

                                html += item;
                            }

                            $albums.append(html);
                        }
                    });

                    $albums.off('click', '>a').on('click', '>a', function () {
                        // 如果当前已经为选中状态则清除
                        if (selectedAlbum === $(this).data('id')) {
                            selectedAlbum = 0;
                        } else {
                            selectedAlbum = $(this).data('id');
                        }
                        resetImages({page: 1, album_id: selectedAlbum});
                        drawer.close();
                    });

                    const resetAlbums = () => {
                        $albums.find('>a').remove();
                        $albums.find(CREATE_ID).addClass('hidden');
                        $albums.find(UPDATE_ID).remove();
                        albumsInfinite.refresh({page: 1});
                    }

                    $albums.off('click', '.update').on('click', '.update', function (e) {
                        e.stopPropagation();
                        let selectedId = $albums.find(UPDATE_ID).data('id');
                        let $item = $(this).closest('a.albums-item');
                        $albums.find(UPDATE_ID).remove();
                        if (selectedId !== $item.data('id')) {
                            $item.after($('#album-update-tpl').html()
                                .replace(/__id__/g, $item.data('id'))
                                .replace(/__name__/g, $item.find('>span').html())
                                .replace(/__intro__/g, $item.attr('title'))
                            );
                        }
                    });

                    $albums.off('click', '.delete').on('click', '.delete', function (e) {
                        e.stopPropagation();
                        // TODO
                        console.log('remove')
                    });

                    // confirm create
                    $albums.off('click', CREATE_ID + ' a').on('click', CREATE_ID + ' a', function (e) {
                        let $form = $(this).siblings('form');
                        axios.post($form.attr('action'), $form.serialize()).then(response => {
                            let $errorMessage = $albums.find(CREATE_ID + ' .error-message').html('').hide();
                            if (response.data.status) {
                                $form.get(0).reset();
                                resetAlbums()
                            } else {
                                $errorMessage.html('<i class="fas fa-exclamation-circle"></i> ' + response.data.message).show();
                            }
                        });
                    });

                    // confirm update
                    $albums.off('click', UPDATE_ID + ' a').on('click', UPDATE_ID + ' a', function (e) {
                        let $form = $(this).siblings('form');
                        axios.put($form.attr('action'), $form.serialize()).then(response => {
                            let $errorMessage = $albums.find(UPDATE_ID + ' .error-message').html('').hide();
                            if (response.data.status) {
                                let $editContainer = $(this).closest(UPDATE_ID);
                                $albums.find(`>a[data-id=${$editContainer.data('id')}]`)
                                    .attr('title', $form.find('textarea').val())
                                    .find('.name').text($form.find('input').val());
                                $editContainer.remove();
                            } else {
                                $errorMessage.html('<i class="fas fa-exclamation-circle"></i> ' + response.data.message).show();
                            }
                        });
                    });
                });
            }

            const setOrderBy = function (sort) {
                resetImages({page: 1, order: sort})
                $('#order span').text({newest: '最新', earliest: '最早', utmost: '最大', least: '最小'}[sort]);
            };

            $('#search').keydown(function (e) {
                if (e.keyCode === 13) {
                    resetImages({page: 1, keyword: $(this).val()});
                }
            });
        </script>
        <script>
            let ds;
            if (! utils.isMobile()) {
                ds = new DragSelect({
                    area: $photos.get(0),
                    keyboardDrag: false,
                });
                ds.subscribe('predragstart', ({ event }) => {
                    if (event.target.id !== 'photos-grid') {
                        ds.break();
                    }
                });
            }

            $photos.on('click', '.photo-selector', function () {
                if (ds) {
                    ds.toggleSelection($(this).closest('a'));
                } else {
                    $(this).closest('a').toggleClass('ds-selected')
                }
            })
        </script>
        <script>
            context.init({
                fadeSpeed: 100,
                filter: function ($obj) {},
                above: 'auto',
                preventDoubleContext: true,
                compress: false
            });
            const methods = {
                view: {text: '查看', action: e => {}},
                refresh: {text: '刷新', action: e => {}},
                open: {text: '新窗口打开', action: e => {}},
                links: {
                    text: '复制链接',
                    subMenu: [
                        {text: 'Url', action: e => {}},
                        {text: 'Html', action: e => {}},
                        {text: 'BBCode', action: e => {}},
                        {text: 'Markdown', action: e => {}},
                        {text: 'Markdown with link', action: e => {}},
                    ],
                },
                detail: {text: '详细信息', action: e => {}},
                rename: {text: '重命名', action: e => {}},
                delete: {text: '删除', action: e => {}},
            };
            $(document).on('contextmenu', '#photos-scroll', function(e) {
                // 点击容器
                context.attach('#photos-grid', [
                    methods.refresh,
                ]);
                // 点击图片
                context.attach('.photos-item', [
                    {header: '图片操作'},
                    methods.refresh,
                    methods.view,
                    methods.open,
                    methods.links,
                    methods.rename,
                    {divider: true},
                    methods.delete,
                ]);
            });
        </script>
    @endpush
</x-app-layout>
